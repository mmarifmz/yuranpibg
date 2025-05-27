<?php

namespace App\Filament\Resources\PaymentFlowResource\Pages;

use App\Filament\Resources\PaymentFlowResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaymentFlows extends ListRecords
{
    protected static string $resource = PaymentFlowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
