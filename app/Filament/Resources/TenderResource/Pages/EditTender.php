<?php

namespace App\Filament\Resources\TenderResource\Pages;

use App\Filament\Resources\TenderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTender extends EditRecord
{
    protected static string $resource = TenderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Handle RFP document metadata
        if (isset($data['rfp_document_path']) && !empty($data['rfp_document_path'])) {
            // Get the uploaded file path
            $filePath = is_array($data['rfp_document_path']) ? $data['rfp_document_path'][0] : $data['rfp_document_path'];
            
            if ($filePath && \Storage::disk('public')->exists($filePath)) {
                $fullPath = \Storage::disk('public')->path($filePath);
                $data['rfp_document_name'] = basename($filePath);
                $data['rfp_document_size'] = \Storage::disk('public')->size($filePath);
                $data['rfp_document_type'] = \Storage::disk('public')->mimeType($filePath);
            }
        }

        // Handle ToR document metadata
        if (isset($data['tor_document_path']) && !empty($data['tor_document_path'])) {
            // Get the uploaded file path
            $filePath = is_array($data['tor_document_path']) ? $data['tor_document_path'][0] : $data['tor_document_path'];
            
            if ($filePath && \Storage::disk('public')->exists($filePath)) {
                $fullPath = \Storage::disk('public')->path($filePath);
                $data['tor_document_name'] = basename($filePath);
                $data['tor_document_size'] = \Storage::disk('public')->size($filePath);
                $data['tor_document_type'] = \Storage::disk('public')->mimeType($filePath);
            }
        }

        $data['updated_by'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
