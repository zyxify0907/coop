<?php

namespace App\Services;

use App\Models\Ahli;
use App\Models\AhliImport;
use App\Models\Pekerja;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use SimpleXMLElement;

class AhliImportService
{
    /**
     * @return array<int, string>
     */
    private const REQUIRED_COLUMNS = ['nama', 'no_matrik', 'semester'];
    private const STUDENT_REQUIRED_COLUMNS = ['nama', 'no_matrik', 'nric', 'kelas', 'tarikh_masuk'];
    private const STAFF_REQUIRED_COLUMNS = ['nama', 'nric'];

    public function import(UploadedFile $file): AhliImportResult
    {
        $filename = $file->getClientOriginalName();

        try {
            $rows = $this->rowsFromFile($file);
        } catch (RuntimeException $exception) {
            return $this->storeResult($filename, 0, [[
                'row' => null,
                'no_matrik' => null,
                'message' => $exception->getMessage(),
            ]]);
        }

        return DB::transaction(function () use ($filename, $rows): AhliImportResult {
            $imported = 0;
            $errors = [];
            $seenNoMatrik = [];

            foreach ($rows as $index => $row) {
                $line = $index + 2;
                $nama = trim((string) ($row['nama'] ?? ''));
                $noMatrik = trim((string) ($row['no_matrik'] ?? ''));
                $semester = $this->normalizeSemester((string) ($row['semester'] ?? ''));

                $message = $this->validateRow($nama, $noMatrik, $semester, $seenNoMatrik);

                if ($message !== null) {
                    $errors[] = [
                        'row' => $line,
                        'no_matrik' => $noMatrik ?: null,
                        'message' => $message,
                    ];

                    continue;
                }

                $seenNoMatrik[$noMatrik] = true;

                Ahli::query()->create([
                    'nama' => $nama,
                    'no_matrik' => $noMatrik,
                    'semester' => $semester,
                    'tarikh_daftar' => now()->toDateString(),
                    'status_aktif' => true,
                ]);

                $imported++;
            }

            return $this->storeResult($filename, $imported, $errors);
        });
    }

    public function importStudents(UploadedFile $file): AhliImportResult
    {
        $filename = $file->getClientOriginalName();

        try {
            $rows = $this->rowsFromFile($file, self::STUDENT_REQUIRED_COLUMNS);
        } catch (RuntimeException $exception) {
            return $this->storeResult($filename, 0, [[
                'row' => null,
                'no_matrik' => null,
                'message' => $exception->getMessage(),
            ]]);
        }

        return DB::transaction(function () use ($filename, $rows): AhliImportResult {
            $imported = 0;
            $errors = [];
            $seenNoMatrik = [];
            $seenNric = [];

            foreach ($rows as $index => $row) {
                $line = $index + 2;
                $nama = trim((string) ($row['nama'] ?? ''));
                $noMatrik = strtoupper(trim((string) ($row['no_matrik'] ?? '')));
                $nric = preg_replace('/\D+/', '', (string) ($row['nric'] ?? '')) ?? '';
                $kelas = strtoupper(trim((string) ($row['kelas'] ?? '')));
                $tarikhMasuk = $this->normalizeDate((string) ($row['tarikh_masuk'] ?? ''));
                $academic = $this->academicFromClass($kelas);

                $message = $this->validateStudentRow($nama, $noMatrik, $nric, $kelas, $tarikhMasuk, $seenNoMatrik, $seenNric);

                if ($message !== null) {
                    $errors[] = [
                        'row' => $line,
                        'no_matrik' => $noMatrik ?: null,
                        'message' => $message,
                    ];

                    continue;
                }

                $seenNoMatrik[$noMatrik] = true;
                $seenNric[$nric] = true;

                Ahli::query()->create([
                    'nama' => $nama,
                    'no_matrik' => $noMatrik,
                    'nric' => $nric,
                    'semester' => $academic['semester'],
                    'program' => $academic['program'],
                    'kelas' => $kelas,
                    'password_hash' => Hash::make($this->generateStudentPassword($noMatrik)),
                    'tarikh_daftar' => $tarikhMasuk,
                    'status_aktif' => true,
                ]);

                $imported++;
            }

            return $this->storeResult($filename, $imported, $errors);
        });
    }

    public function importStaff(UploadedFile $file): AhliImportResult
    {
        $filename = $file->getClientOriginalName();

        try {
            $rows = $this->rowsFromFile($file, self::STAFF_REQUIRED_COLUMNS);
        } catch (RuntimeException $exception) {
            return $this->storeResult($filename, 0, [[
                'row' => null,
                'no_matrik' => null,
                'message' => $exception->getMessage(),
            ]]);
        }

        return DB::transaction(function () use ($filename, $rows): AhliImportResult {
            $imported = 0;
            $errors = [];
            $seenNoPekerja = [];
            $seenNric = [];
            $seenEmail = [];
            $nextStaffNumber = $this->nextStaffNumber();

            foreach ($rows as $index => $row) {
                $line = $index + 2;
                $nama = trim((string) ($row['nama'] ?? ''));
                $noPekerja = strtoupper(trim((string) ($row['no_pekerja'] ?? '')));
                $nric = preg_replace('/\D+/', '', (string) ($row['nric'] ?? '')) ?? '';
                $staffType = $this->normalizeStaffType((string) ($row['staff_type'] ?? '')) ?? 'lecturer_member';
                $email = trim((string) ($row['email'] ?? ''));
                $noTel = trim((string) ($row['no_tel'] ?? ''));
                $tarikhMula = $this->normalizeDate((string) ($row['tarikh_masuk'] ?? $row['tarikh_mula'] ?? '')) ?? now()->toDateString();
                $existingStaff = $nric !== '' ? Pekerja::query()->where('nric', $nric)->first() : null;

                if ($noPekerja === '') {
                    $noPekerja = $existingStaff?->no_pekerja ?: $this->formatStaffNumber($nextStaffNumber++);
                }

                $existingStaff ??= Pekerja::query()->where('no_pekerja', $noPekerja)->first();

                $message = $this->validateStaffRow($nama, $noPekerja, $nric, $staffType, $email, $seenNoPekerja, $seenNric, $seenEmail, $existingStaff?->id_pekerja);

                if ($message !== null) {
                    $errors[] = [
                        'row' => $line,
                        'no_matrik' => $noPekerja ?: null,
                        'message' => $message,
                    ];

                    continue;
                }

                $seenNoPekerja[$noPekerja] = true;
                $seenNric[$nric] = true;

                if ($email !== '') {
                    $seenEmail[strtolower($email)] = true;
                }

                $staffData = [
                    'no_pekerja' => $noPekerja,
                    'nama' => $nama,
                    'nric' => $nric,
                    'staff_type' => $staffType,
                    'no_tel' => $noTel !== '' ? $noTel : null,
                    'email' => $email !== '' ? $email : null,
                    'kadar_elaun' => 0,
                    'tarikh_mula' => $tarikhMula,
                    'password_hash' => Hash::make('staff12345'),
                    'status_aktif' => true,
                ];

                if ($existingStaff) {
                    unset($staffData['password_hash']);
                    $existingStaff->update($staffData);
                } else {
                    Pekerja::query()->create($staffData);
                }

                $imported++;
            }

            return $this->storeResult($filename, $imported, $errors);
        });
    }

    private function validateRow(string $nama, string $noMatrik, string $semester, array $seenNoMatrik): ?string
    {
        if ($nama === '') {
            return 'Nama cannot be empty.';
        }

        if ($noMatrik === '') {
            return 'No Matrik cannot be empty.';
        }

        if ($semester === '' || ! preg_match('/^Sem [1-9][0-9]*$/', $semester)) {
            return 'Semester must use valid format, e.g. Sem 1 or Sem 2.';
        }

        if (isset($seenNoMatrik[$noMatrik])) {
            return 'Duplicate No Matrik in import file; skipped.';
        }

        if (Ahli::query()->where('no_matrik', $noMatrik)->exists()) {
            return 'Duplicate No Matrik already exists; skipped.';
        }

        return null;
    }

    private function validateStaffRow(string $nama, string $noPekerja, string $nric, ?string $staffType, string $email, array $seenNoPekerja, array $seenNric, array $seenEmail, ?int $existingStaffId = null): ?string
    {
        if ($nama === '') {
            return 'Nama tidak boleh kosong.';
        }

        if ($noPekerja === '') {
            return 'No Pekerja tidak boleh kosong.';
        }

        if (! preg_match('/^PBT-\d+$/', $noPekerja)) {
            return 'No Pekerja mesti dalam format PBT-1, PBT-2 dan seterusnya.';
        }

        if ($nric === '') {
            return 'No KP tidak boleh kosong.';
        }

        if (isset($seenNoPekerja[$noPekerja])) {
            return 'No Pekerja berulang dalam fail import; dilangkau.';
        }

        if (isset($seenNric[$nric])) {
            return 'No KP berulang dalam fail import; dilangkau.';
        }

        if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Email tidak sah.';
        }

        if ($email !== '' && isset($seenEmail[strtolower($email)])) {
            return 'Email berulang dalam fail import; dilangkau.';
        }

        if (Pekerja::query()->where('no_pekerja', $noPekerja)->when($existingStaffId, fn ($query) => $query->where('id_pekerja', '!=', $existingStaffId))->exists()) {
            return 'No Pekerja sudah wujud dalam sistem; dilangkau.';
        }

        if (Pekerja::query()->where('nric', $nric)->when($existingStaffId, fn ($query) => $query->where('id_pekerja', '!=', $existingStaffId))->exists()) {
            return 'No KP sudah wujud dalam sistem; dilangkau.';
        }

        if ($email !== '' && Pekerja::query()->where('email', $email)->when($existingStaffId, fn ($query) => $query->where('id_pekerja', '!=', $existingStaffId))->exists()) {
            return 'Email sudah wujud dalam sistem; dilangkau.';
        }

        return null;
    }

    private function validateStudentRow(string $nama, string $noMatrik, string $nric, string $kelas, ?string $tarikhMasuk, array $seenNoMatrik, array $seenNric): ?string
    {
        if ($nama === '') {
            return 'Nama tidak boleh kosong.';
        }

        if ($noMatrik === '') {
            return 'No Matrik tidak boleh kosong.';
        }

        if ($nric === '') {
            return 'No KP tidak boleh kosong.';
        }

        if ($kelas === '') {
            return 'Kelas tidak boleh kosong.';
        }

        if ($this->academicFromClass($kelas) === null) {
            return 'Kelas mesti dalam format DIT1A, DIT1B, DDC1A atau DBF1A hingga semester 6.';
        }

        if ($tarikhMasuk === null) {
            return 'Tarikh Masuk tidak sah.';
        }

        if (isset($seenNoMatrik[$noMatrik])) {
            return 'No Matrik berulang dalam fail import; dilangkau.';
        }

        if (isset($seenNric[$nric])) {
            return 'No KP berulang dalam fail import; dilangkau.';
        }

        if (Ahli::query()->where('no_matrik', $noMatrik)->exists()) {
            return 'No Matrik sudah wujud dalam sistem; dilangkau.';
        }

        if (Ahli::query()->where('nric', $nric)->exists()) {
            return 'No KP sudah wujud dalam sistem; dilangkau.';
        }

        return null;
    }

    private function normalizeSemester(string $semester): string
    {
        $semester = trim(preg_replace('/\s+/', ' ', $semester) ?? '');

        if (preg_match('/^sem\s+([1-9][0-9]*)$/i', $semester, $matches)) {
            return 'Sem '.$matches[1];
        }

        return $semester;
    }

    /**
     * @return array{semester: string, program: string}|null
     */
    private function academicFromClass(string $kelas): ?array
    {
        if (! preg_match('/^(DIT|DDC|DBF)([1-6])[A-Z]$/', strtoupper(trim($kelas)), $matches)) {
            return null;
        }

        return [
            'semester' => 'Sem '.$matches[2],
            'program' => $matches[1] === 'DIT' ? 'JTMK' : 'JRKV',
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function rowsFromFile(UploadedFile $file, array $requiredColumns = self::REQUIRED_COLUMNS): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        return match ($extension) {
            'csv', 'txt' => $this->rowsFromCsv($file->getRealPath(), $requiredColumns),
            'xlsx' => $this->rowsFromXlsx($file->getRealPath(), $requiredColumns),
            default => throw new RuntimeException('File format must be CSV or Excel (.xlsx).'),
        };
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function rowsFromCsv(string $path, array $requiredColumns): array
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException('Unable to read uploaded file.');
        }

        $values = [];

        while (($row = fgetcsv($handle)) !== false) {
            if ($this->isEmptyRow($row)) {
                continue;
            }

            $values[] = $row;
        }

        fclose($handle);

        if ($values === []) {
            throw new RuntimeException('Uploaded file is empty.');
        }

        return $this->rowsFromTabularValues($values, $requiredColumns);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function rowsFromXlsx(string $path, array $requiredColumns): array
    {
        $sharedStringsXml = $this->xlsxEntry($path, 'xl/sharedStrings.xml');
        $sheet = $this->xlsxEntry($path, 'xl/worksheets/sheet1.xml');

        if ($sheet === false) {
            throw new RuntimeException('Excel file must contain a first worksheet.');
        }

        $sharedStrings = $this->readSharedStrings($sharedStringsXml ?: null);
        $values = $this->readWorksheetValues($sheet, $sharedStrings);

        if ($values === []) {
            throw new RuntimeException('Uploaded file is empty.');
        }

        return $this->rowsFromTabularValues($values, $requiredColumns);
    }

    private function xlsxEntry(string $path, string $entryName): string|false
    {
        if (class_exists(\ZipArchive::class)) {
            $zip = new \ZipArchive;

            if ($zip->open($path) !== true) {
                throw new RuntimeException('Unable to read Excel file.');
            }

            $contents = $zip->getFromName($entryName);
            $zip->close();

            return $contents;
        }

        return $this->xlsxEntryWithoutZipExtension($path, $entryName);
    }

    private function xlsxEntryWithoutZipExtension(string $path, string $entryName): string|false
    {
        $binary = file_get_contents($path);

        if ($binary === false) {
            throw new RuntimeException('Unable to read Excel file.');
        }

        $endOffset = strrpos($binary, "PK\x05\x06");

        if ($endOffset === false) {
            throw new RuntimeException('Unable to read Excel file structure.');
        }

        $endRecord = substr($binary, $endOffset + 4, 18);
        $end = unpack('vdisk/vstartDisk/ventriesDisk/ventries/Vsize/Voffset/vcommentLength', $endRecord);

        if (! is_array($end)) {
            throw new RuntimeException('Unable to read Excel file structure.');
        }

        $position = (int) $end['offset'];
        $centralEnd = $position + (int) $end['size'];

        while ($position < $centralEnd) {
            if (substr($binary, $position, 4) !== "PK\x01\x02") {
                break;
            }

            $header = unpack(
                'vversionMade/vversionNeeded/vflags/vmethod/vtime/vdate/Vcrc/VcompressedSize/VuncompressedSize/vnameLength/vextraLength/vcommentLength/vdiskStart/vinternalAttrs/VexternalAttrs/VlocalOffset',
                substr($binary, $position + 4, 42)
            );

            if (! is_array($header)) {
                break;
            }

            $nameLength = (int) $header['nameLength'];
            $extraLength = (int) $header['extraLength'];
            $commentLength = (int) $header['commentLength'];
            $name = substr($binary, $position + 46, $nameLength);

            if ($name === $entryName) {
                return $this->readXlsxLocalEntry($binary, (int) $header['localOffset'], (int) $header['method'], (int) $header['compressedSize']);
            }

            $position += 46 + $nameLength + $extraLength + $commentLength;
        }

        return false;
    }

    private function readXlsxLocalEntry(string $binary, int $offset, int $method, int $compressedSize): string
    {
        if (substr($binary, $offset, 4) !== "PK\x03\x04") {
            throw new RuntimeException('Unable to read Excel worksheet data.');
        }

        $header = unpack('vversion/vflags/vmethod/vtime/vdate/Vcrc/VcompressedSize/VuncompressedSize/vnameLength/vextraLength', substr($binary, $offset + 4, 26));

        if (! is_array($header)) {
            throw new RuntimeException('Unable to read Excel worksheet data.');
        }

        $dataStart = $offset + 30 + (int) $header['nameLength'] + (int) $header['extraLength'];
        $compressed = substr($binary, $dataStart, $compressedSize);

        if ($method === 0) {
            return $compressed;
        }

        if ($method === 8) {
            $data = gzinflate($compressed);

            if ($data === false) {
                throw new RuntimeException('Unable to unzip Excel worksheet data.');
            }

            return $data;
        }

        throw new RuntimeException('Excel compression method is not supported.');
    }

    /**
     * @return array<int, string>
     */
    private function readSharedStrings(?string $xml): array
    {
        if (! $xml) {
            return [];
        }

        $strings = [];
        $document = simplexml_load_string($xml);

        if (! $document instanceof SimpleXMLElement) {
            return [];
        }

        foreach ($document->si as $string) {
            $text = '';

            foreach ($string->xpath('.//t') ?: [] as $node) {
                $text .= (string) $node;
            }

            $strings[] = trim($text !== '' ? $text : (string) $string->t);
        }

        return $strings;
    }

    /**
     * @param  array<int, string>  $sharedStrings
     * @return array<int, array<int, string>>
     */
    private function readWorksheetValues(string $xml, array $sharedStrings): array
    {
        $document = simplexml_load_string($xml);

        if (! $document instanceof SimpleXMLElement) {
            throw new RuntimeException('Unable to parse Excel worksheet.');
        }

        $rows = [];

        foreach ($document->sheetData->row as $row) {
            $values = [];

            foreach ($row->c as $cell) {
                $reference = (string) $cell['r'];
                $columnIndex = $this->columnIndex($reference);
                $type = (string) $cell['t'];
                $value = (string) $cell->v;

                if ($type === 's') {
                    $value = $sharedStrings[(int) $value] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = (string) $cell->is->t;
                }

                $values[$columnIndex] = trim($value);
            }

            if ($values !== []) {
                ksort($values);
                $rows[] = $this->fillMissingColumns($values);
            }
        }

        return $rows;
    }

    private function columnIndex(string $reference): int
    {
        preg_match('/^[A-Z]+/i', $reference, $matches);
        $letters = strtoupper($matches[0] ?? 'A');
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return $index - 1;
    }

    /**
     * @param  array<int, string>  $values
     * @return array<int, string>
     */
    private function fillMissingColumns(array $values): array
    {
        $filled = [];
        $lastIndex = max(array_keys($values));

        for ($index = 0; $index <= $lastIndex; $index++) {
            $filled[] = $values[$index] ?? '';
        }

        return $filled;
    }

    /**
     * @param  array<int, array<int, string>>  $values
     * @param  array<int, string>  $requiredColumns
     * @return array<int, array<string, string>>
     */
    private function rowsFromTabularValues(array $values, array $requiredColumns): array
    {
        $headerIndex = null;
        $headers = [];

        foreach ($values as $index => $row) {
            $normalized = $this->normalizeHeaders($row);
            $missing = array_diff($requiredColumns, $normalized);

            if ($missing === []) {
                $headerIndex = $index;
                $headers = $normalized;
                break;
            }
        }

        if ($headerIndex === null) {
            $firstRowHeaders = $this->normalizeHeaders($values[0] ?? []);
            $this->assertRequiredHeaders($firstRowHeaders, $requiredColumns);
        }

        $rows = [];

        foreach (array_slice($values, $headerIndex + 1) as $row) {
            if ($this->isEmptyRow($row)) {
                continue;
            }

            $rows[] = $this->combineRow($headers, $row);
        }

        return $rows;
    }

    /**
     * @param  array<int, string>  $headers
     * @return array<int, string>
     */
    private function normalizeHeaders(array $headers): array
    {
        return array_map(function (string $header): string {
            $header = strtolower(trim($header, " \t\n\r\0\x0B\xEF\xBB\xBF"));
            $header = preg_replace('/[^a-z0-9]+/', '_', $header) ?? $header;
            $header = match (trim($header, '_')) {
                'no_kp', 'no_k_p', 'nokp', 'nric', 'ic' => 'nric',
                'nom_matriks', 'no_matriks', 'nom_matrik', 'no_matrik', 'matrik' => 'no_matrik',
                'tarikh_masuk', 'tarikh_daftar', 'tarikh' => 'tarikh_masuk',
                'no_pekerja', 'no_staff', 'staff_no', 'no_staf' => 'no_pekerja',
                'jenis_staff', 'jenis_staf', 'staff_type', 'type_staff', 'kategori_staff', 'kategori_staf' => 'staff_type',
                'telefon', 'no_telefon', 'phone', 'tel', 'no_tel' => 'no_tel',
                'tarikh_mula', 'tarikh_lantik', 'tarikh_kerja' => 'tarikh_mula',
                default => trim($header, '_'),
            };

            return $header;
        }, $headers);
    }

    /**
     * @param  array<int, string>  $headers
     */
    private function assertRequiredHeaders(array $headers, array $requiredColumns): void
    {
        $missing = array_diff($requiredColumns, $headers);

        if ($missing !== []) {
            throw new RuntimeException('Missing required columns: '.implode(', ', $missing).'.');
        }
    }

    private function normalizeDate(string $value): ?string
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $days = (int) floor((float) $value);

            if ($days > 0) {
                return Carbon::create(1899, 12, 30)->addDays($days)->toDateString();
            }
        }

        foreach (['d-M-y', 'd-M-Y', 'd/m/Y', 'd/m/y', 'Y-m-d', 'm/d/Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->toDateString();
            } catch (\Throwable) {
                //
            }
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, string>  $values
     * @return array<string, string>
     */
    private function combineRow(array $headers, array $values): array
    {
        $row = [];

        foreach ($headers as $index => $header) {
            $row[$header] = trim((string) ($values[$index] ?? ''));
        }

        return $row;
    }

    /**
     * @param  array<int, string>  $values
     */
    private function isEmptyRow(array $values): bool
    {
        return collect($values)->every(fn ($value): bool => trim((string) $value) === '');
    }

    /**
     * @param  array<int, array{row: int|null, no_matrik: string|null, message: string}>  $errors
     */
    private function storeResult(string $filename, int $imported, array $errors): AhliImportResult
    {
        $log = AhliImport::query()->create([
            'filename' => $filename,
            'imported_count' => $imported,
            'failed_count' => count($errors),
            'errors' => $errors,
        ]);

        return new AhliImportResult($imported, count($errors), $log);
    }

    private function generateStudentPassword(string $noMatrik): string
    {
        return strtoupper(trim($noMatrik)).'@123';
    }

    private function normalizeStaffType(string $value): ?string
    {
        $normalized = strtolower(trim(preg_replace('/[^a-z0-9]+/', '_', $value) ?? ''));
        $normalized = trim($normalized, '_');

        return match ($normalized) {
            'lecturer_member', 'lecturer', 'pensyarah', 'pensyarah_staf_akademik', 'staf_akademik', 'staff_akademik' => 'lecturer_member',
            'clothing_staff', 'baju', 'staff_baju', 'staf_baju', 'pengurusan_baju' => 'clothing_staff',
            default => null,
        };
    }

    private function nextStaffNumber(): int
    {
        $numbers = Pekerja::query()
            ->where('no_pekerja', 'like', 'PBT-%')
            ->pluck('no_pekerja')
            ->map(fn ($value): int => (int) preg_replace('/\D+/', '', (string) $value))
            ->filter(fn (int $number): bool => $number > 0);

        return ((int) $numbers->max()) + 1;
    }

    private function formatStaffNumber(int $number): string
    {
        return 'PBT-'.$number;
    }
}
