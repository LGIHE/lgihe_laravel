<?php

namespace App\Console\Commands;

use App\Models\Tender;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class FixTenderDocumentMetadata extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tender:fix-metadata';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix null document metadata for existing tenders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fixing tender document metadata...');

        $tenders = Tender::withTrashed()->get();
        $fixed = 0;

        foreach ($tenders as $tender) {
            $updated = false;

            // Fix RFP document metadata
            if ($tender->rfp_document_path && Storage::disk('public')->exists($tender->rfp_document_path)) {
                if (!$tender->rfp_document_name || !$tender->rfp_document_size || !$tender->rfp_document_type) {
                    $tender->rfp_document_name = basename($tender->rfp_document_path);
                    $tender->rfp_document_size = Storage::disk('public')->size($tender->rfp_document_path);
                    $tender->rfp_document_type = Storage::disk('public')->mimeType($tender->rfp_document_path);
                    $updated = true;
                    $this->line("Fixed RFP metadata for tender: {$tender->title}");
                }
            }

            // Fix ToR document metadata
            if ($tender->tor_document_path && Storage::disk('public')->exists($tender->tor_document_path)) {
                if (!$tender->tor_document_name || !$tender->tor_document_size || !$tender->tor_document_type) {
                    $tender->tor_document_name = basename($tender->tor_document_path);
                    $tender->tor_document_size = Storage::disk('public')->size($tender->tor_document_path);
                    $tender->tor_document_type = Storage::disk('public')->mimeType($tender->tor_document_path);
                    $updated = true;
                    $this->line("Fixed ToR metadata for tender: {$tender->title}");
                }
            }

            if ($updated) {
                $tender->saveQuietly(); // Save without triggering events
                $fixed++;
            }
        }

        $this->info("Fixed metadata for {$fixed} tender(s).");

        return Command::SUCCESS;
    }
}
