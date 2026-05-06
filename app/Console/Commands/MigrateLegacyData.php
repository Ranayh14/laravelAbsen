<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateLegacyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-legacy-data {file? : The path to the legacy SQL file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate data from legacy SQL file to Laravel schema using a robust line-by-line parser';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(600); // 10 minutes
        
        $filePath = $this->argument('file') ?? 'd:\\xampp\\htdocs\\Magang\\Absen\\database_backup\\absen_db_backup.sql';

        if (!file_exists($filePath)) {
            $this->error("File not found: $filePath");
            return 1;
        }

        $this->info("Reading legacy data from $filePath...");
        
        $handle = fopen($filePath, "r");
        if (!$handle) {
            $this->error("Could not open file: $filePath");
            return 1;
        }

        // Turn off foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $this->info("Processing tables...");

        $tables = [
            'users' => 'users',
            'attendance' => 'attendance',
            'daily_reports' => 'daily_reports',
            'monthly_reports' => 'monthly_reports',
            'admin_help_requests' => 'admin_help_requests',
            'attendance_notes' => 'attendance_notes',
            'manual_holidays' => 'manual_holidays',
            'settings' => 'settings',
            'employee_work_schedule' => 'employee_work_schedule'
        ];

        foreach ($tables as $legacyTable => $laravelTable) {
            $this->info("Truncating $laravelTable...");
            DB::table($laravelTable)->delete();
            
            $this->migrateTable($handle, $legacyTable, $laravelTable);
            // Reset file pointer for next table
            fseek($handle, 0);
        }

        fclose($handle);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info("Legacy data migration completed successfully!");
        return 0;
    }

    private function migrateTable($handle, $legacyTable, $laravelTable)
    {
        $this->info("Migrating $legacyTable to $laravelTable...");
        $count = 0;
        $insertIntoStart = "INSERT INTO `$legacyTable` (";
        $insertIgnoreStart = "INSERT IGNORE INTO `$legacyTable` (";
        
        $columns = [];
        $statementBuffer = "";
        $isCapturing = false;

        while (($line = fgets($handle)) !== false) {
            $trimmedLine = trim($line);
            if (empty($trimmedLine) || str_starts_with($trimmedLine, "--") || str_starts_with($trimmedLine, "/*")) continue;

            if (str_starts_with($trimmedLine, $insertIntoStart) || str_starts_with($trimmedLine, $insertIgnoreStart)) {
                $isCapturing = true;
                
                // Extract columns
                $startPos = strpos($trimmedLine, "(") + 1;
                $endPos = strpos($trimmedLine, ") VALUES");
                if ($endPos === false) {
                    $endPos = strpos($trimmedLine, ")  VALUES");
                }
                
                if ($endPos !== false) {
                    $colString = substr($trimmedLine, $startPos, $endPos - $startPos);
                    $columns = array_map(function($c) { return trim($c, " `"); }, explode(',', $colString));
                    
                    $valuesPart = substr($trimmedLine, $endPos + strlen(") VALUES"));
                    $statementBuffer = trim($valuesPart);
                } else {
                    $isCapturing = false; 
                }
            } elseif ($isCapturing) {
                $statementBuffer .= " " . $trimmedLine;
            }

            if ($isCapturing && str_ends_with(trim($statementBuffer), ";")) {
                $valuesString = rtrim(trim($statementBuffer), ';');
                $rows = $this->robustSplitValues($valuesString);
                
                foreach ($rows as $rowValues) {
                    if (empty($rowValues)) continue;
                    
                    $data = [];
                    foreach ($columns as $i => $col) {
                        $val = isset($rowValues[$i]) ? trim($rowValues[$i]) : null;
                        $data[$col] = $this->cleanValue($val);
                    }

                    $this->processRow($laravelTable, $data);
                    $count++;
                }
                $isCapturing = false;
                $statementBuffer = "";
            }
        }

        $this->line(" - Imported $count records into $laravelTable.");
    }

    private function cleanValue($val)
    {
        if ($val === 'NULL' || $val === null || $val === '' || strtolower($val) === 'null') {
            return null;
        }
        
        if ((str_starts_with($val, "'") && str_ends_with($val, "'")) || 
            (str_starts_with($val, '"') && str_ends_with($val, '"'))) {
            $val = substr($val, 1, -1);
        }

        return str_replace(
            ["\\'", "\\\"", "\\\\", "\\n", "\\r", "\\t"],
            ["'", "\"", "\\", "\n", "\r", "\t"],
            $val
        );
    }

    private function robustSplitValues($valuesString)
    {
        $rows = [];
        $length = strlen($valuesString);
        $currentRow = [];
        $currentVal = "";
        $inString = false;
        $quoteChar = "";
        $inRow = false;
        $escaped = false;

        for ($i = 0; $i < $length; $i++) {
            $char = $valuesString[$i];

            if ($escaped) {
                $currentVal .= $char;
                $escaped = false;
                continue;
            }

            if ($char === "\\") {
                $currentVal .= $char;
                $escaped = true;
                continue;
            }

            if ($char === "(" && !$inString && !$inRow) {
                $inRow = true;
                $currentRow = [];
                $currentVal = "";
                continue;
            }

            if ($char === ")" && !$inString && $inRow) {
                $currentRow[] = $currentVal;
                $rows[] = $currentRow;
                $inRow = false;
                $currentVal = "";
                continue;
            }

            if (($char === "'" || $char === '"') && $inRow) {
                if (!$inString) {
                    $inString = true;
                    $quoteChar = $char;
                    $currentVal .= $char;
                } elseif ($char === $quoteChar) {
                    $inString = false;
                    $currentVal .= $char;
                } else {
                    $currentVal .= $char;
                }
                continue;
            }

            if ($char === "," && $inRow && !$inString) {
                $currentRow[] = $currentVal;
                $currentVal = "";
                continue;
            }

            if ($inRow) {
                $currentVal .= $char;
            }
        }

        return $rows;
    }

    private function processRow($laravelTable, $data)
    {
        $tableColumns = Schema::getColumnListing($laravelTable);
        $filteredData = array_intersect_key($data, array_flip($tableColumns));

        switch ($laravelTable) {
            case 'users':
                $lookupKey = isset($data['id']) ? ['id' => $data['id']] : ['email' => $data['email']];
                DB::table('users')->updateOrInsert(
                    $lookupKey,
                    array_merge($filteredData, [
                        'password' => $data['password_hash'] ?? ($data['password'] ?? null),
                        'updated_at' => now(),
                    ])
                );
                break;
            case 'attendance':
                if (isset($filteredData['ket'])) {
                    if ($filteredData['ket'] == 'hadir') $filteredData['ket'] = 'wfo';
                    if ($filteredData['ket'] == 'wfh') $filteredData['ket'] = 'wfa';
                }
                DB::table('attendance')->updateOrInsert(['id' => $data['id']], array_merge($filteredData, ['updated_at' => now()]));
                break;
            case 'monthly_reports':
                if (isset($filteredData['status']) && $filteredData['status'] == 'submitted') {
                    $filteredData['status'] = 'belum di approve';
                }
                DB::table('monthly_reports')->updateOrInsert(['id' => $data['id']], array_merge($filteredData, ['updated_at' => now()]));
                break;
            default:
                if (isset($data['id'])) {
                    DB::table($laravelTable)->updateOrInsert(['id' => $data['id']], array_merge($filteredData, ['updated_at' => now()]));
                } else {
                    DB::table($laravelTable)->insert(array_merge($filteredData, ['updated_at' => now()]));
                }
                break;
        }
    }
}
