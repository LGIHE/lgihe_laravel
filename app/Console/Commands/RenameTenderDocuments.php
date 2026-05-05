<?php

namespace App\Console\Commands;

use App\Models\Tender;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RenameTenderDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tender:rename-documents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rename tender documents to follow the new naming convention (slug_RFP.ext and slug_ToR.ext)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Renaming tender documents...');

        $tenders = Tender::withTrashed()->get();
        $renamed = 0;

        foreach ($tenders as $tender) {
            $updated = false;

            // Rename RFP document
            if ($tender->rfp_document_path && Storage::disk('public')->exists($tender->rfp_document_path)) {
                $oldPath = $tender->rfp_document_path;
                $extension = pathinfo($oldPath, PATHINFO_EXTENSION);
                $newFileName = "{$tender->slug}_RFP.{$extension}";
                $newPath = "tender-documents/rfp/{$newFileName}";

                // Only rename if the name is different
                if ($oldPath !== $newPath) {
                    // Copy to new location
                    Storage::disk('public')->copy($oldPath, $newPath);
                    
                    // Delete old file
                    Storage::disk('public')->delete($oldPath);
                    
                    // Update database
                    $tender->rfp_document_path = $newPath;
                    $tender->rfp_document_name = $newFileName;
                    $updated = true;
                    
                    $this->line("Renamed RFP: {$oldPath} -> {$newPath}");
                }
            }

            // Rename ToR document
            if ($tender->tor_document_path && Storage::disk('public')->exists($tender->tor_document_path)) {
                $oldPath = $tender->tor_document_path;
                $extension = pathinfo($oldPath, PATHINFO_EXTENSION);
                $newFileName = "{$tender->slug}_ToR.{$extension}";
                $newPath = "tender-documents/tor/{$newFileName}";

                // Only rename if the name is different
                if ($oldPath !== $newPath) {
                    // Copy to new location
                    Storage::disk('public')->copy($oldPath, $newPath);
                    
                    // Delete old file
                    Storage::disk('public')->delete($oldPath);
                    
                    // Update database
                    $tender->tor_document_path = $newPath;
                    $tender->tor_document_name = $newFileName;
                    $updated = true;
                    
                    $this->line("Renamed ToR: {$oldPath} -> {$newPath}");
                }
            }

            if ($updated) {
                $tender->saveQuietly(); // Save without triggering events
                $renamed++;
            }
        }

        $this->info("Renamed documents for {$renamed} tender(s).");

        return Command::SUCCESS;
    }
}
