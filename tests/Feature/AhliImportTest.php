<?php

namespace Tests\Feature;

use App\Models\Ahli;
use App\Models\AhliImport;
use App\Notifications\AhliImportCompleted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AhliImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_import_valid_csv_ahli_records(): void
    {
        Notification::fake();

        $file = $this->csvUpload("Nama,No Matrik,Semester\nAli Ahmad,A001,Sem 1\nSiti Aminah,A002,sem 2\n");

        $response = $this->post(route('admin.ahli.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.ahli.index'));
        $response->assertSessionHas('status', 'Berjaya mengimport 2 rekod. Gagal: 0 rekod.');

        $this->assertDatabaseHas('ahli', [
            'no_matrik' => 'A001',
            'nama' => 'Ali Ahmad',
            'semester' => 'Sem 1',
        ]);

        $this->assertDatabaseHas('ahli', [
            'no_matrik' => 'A002',
            'semester' => 'Sem 2',
        ]);

        Notification::assertSentOnDemand(AhliImportCompleted::class);
    }

    public function test_import_skips_duplicate_no_matrik_and_logs_error_report(): void
    {
        Notification::fake();

        Ahli::query()->create([
            'no_matrik' => 'A001',
            'nama' => 'Existing',
            'semester' => 'Sem 1',
        ]);

        $file = $this->csvUpload("Nama,No Matrik,Semester\nAli Ahmad,A001,Sem 1\nSiti Aminah,A002,Sem 2\nDuplicate,A002,Sem 2\n");

        $response = $this->post(route('admin.ahli.import'), [
            'file' => $file,
        ]);

        $response->assertSessionHas('status', 'Berjaya mengimport 1 rekod. Gagal: 2 rekod.');

        $this->assertDatabaseCount('ahli', 2);

        $log = AhliImport::query()->latest()->firstOrFail();

        $this->assertSame(1, $log->imported_count);
        $this->assertSame(2, $log->failed_count);
        $this->assertSame('Duplicate No Matrik already exists; skipped.', $log->errors[0]['message']);
        $this->assertSame('Duplicate No Matrik in import file; skipped.', $log->errors[1]['message']);
    }

    public function test_import_validates_required_fields_and_semester_format(): void
    {
        Notification::fake();

        $file = $this->csvUpload("Nama,No Matrik,Semester\n,A001,Sem 1\nBad Semester,A002,Semester 2\n");

        $response = $this->post(route('admin.ahli.import'), [
            'file' => $file,
        ]);

        $response->assertSessionHas('status', 'Berjaya mengimport 0 rekod. Gagal: 2 rekod.');

        $this->assertDatabaseCount('ahli', 0);

        $log = AhliImport::query()->latest()->firstOrFail();

        $this->assertSame('Nama cannot be empty.', $log->errors[0]['message']);
        $this->assertSame('Semester must use valid format, e.g. Sem 1 or Sem 2.', $log->errors[1]['message']);
    }

    public function test_admin_can_import_valid_xlsx_ahli_records(): void
    {
        if (! class_exists(\ZipArchive::class)) {
            $this->markTestSkipped('The PHP zip extension is required to build and parse xlsx files.');
        }

        Notification::fake();

        $file = $this->xlsxUpload([
            ['Nama', 'No Matrik', 'Semester'],
            ['Ali Ahmad', 'X001', 'Sem 1'],
            ['Siti Aminah', 'X002', 'Sem 2'],
        ]);

        $response = $this->post(route('admin.ahli.import'), [
            'file' => $file,
        ]);

        $response->assertSessionHas('status', 'Berjaya mengimport 2 rekod. Gagal: 0 rekod.');

        $this->assertDatabaseHas('ahli', [
            'no_matrik' => 'X001',
            'nama' => 'Ali Ahmad',
        ]);

        $this->assertDatabaseHas('ahli', [
            'no_matrik' => 'X002',
            'nama' => 'Siti Aminah',
        ]);
    }

    private function csvUpload(string $contents): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'ahli-import-');
        file_put_contents($path, $contents);

        return new UploadedFile($path, 'ahli.csv', 'text/csv', null, true);
    }

    /**
     * @param  array<int, array<int, string>>  $rows
     */
    private function xlsxUpload(array $rows): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'ahli-import-xlsx-');
        $zip = new \ZipArchive;
        $zip->open($path, \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="xml" ContentType="application/xml"/></Types>');
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->worksheetXml($rows));
        $zip->close();

        return new UploadedFile($path, 'ahli.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    /**
     * @param  array<int, array<int, string>>  $rows
     */
    private function worksheetXml(array $rows): string
    {
        $xmlRows = [];

        foreach ($rows as $rowIndex => $row) {
            $cells = [];

            foreach ($row as $columnIndex => $value) {
                $reference = $this->columnName($columnIndex + 1).($rowIndex + 1);
                $escaped = htmlspecialchars($value, ENT_XML1);
                $cells[] = '<c r="'.$reference.'" t="inlineStr"><is><t>'.$escaped.'</t></is></c>';
            }

            $xmlRows[] = '<row r="'.($rowIndex + 1).'">'.implode('', $cells).'</row>';
        }

        return '<?xml version="1.0" encoding="UTF-8"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>'.implode('', $xmlRows).'</sheetData></worksheet>';
    }

    private function columnName(int $number): string
    {
        $name = '';

        while ($number > 0) {
            $number--;
            $name = chr(65 + ($number % 26)).$name;
            $number = intdiv($number, 26);
        }

        return $name;
    }
}
