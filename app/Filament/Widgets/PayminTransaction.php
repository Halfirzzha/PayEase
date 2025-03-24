<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class PayminTransaction extends BaseWidget
{
    use HasWidgetShield;

    protected static ?string $heading = 'Transaction History Student';
    protected int | string | array $columnSpan = 'full';

    /**
     * Define the table schema for the widget.
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::query()
                    ->with(['user', 'department']) // Eager load relationships
                    ->orderBy('created_at', 'DESC') // Sort by latest transactions
            )
            ->columns([
                // Transaction Code
                Tables\Columns\TextColumn::make('code')
                    ->label('Transaction Code')
                    ->searchable()
                    ->tooltip('Unique code for the transaction'),

                // User Name
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->tooltip('Name of the user who made the transaction'),

                // Department Name
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Department')
                    ->tooltip('Department associated with the transaction'),

                // Semester
                Tables\Columns\TextColumn::make('department.semester')
                    ->label('Semester')
                    ->tooltip('Semester associated with the department'),

                // Payment Method
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state)))
                    ->tooltip('Method used for payment'),

                // Payment Status
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'success' => 'success',
                        'failed' => 'danger',
                        'complete' => 'primary', // Gunakan 'complete' sesuai ENUM di database
                        default => 'secondary',
                    })
                    ->tooltip('Current status of the payment'),

                // Payment Proof
                Tables\Columns\ImageColumn::make('payment_proof')
                    ->label('Payment Proof')
                    ->width(150) // Adjusted width for better UI
                    ->height(100)
                    ->tooltip('Proof of payment uploaded by the user'),

                // Cost
                Tables\Columns\TextColumn::make('department.cost')
                    ->label('Cost')
                    ->money('IDR')
                    ->tooltip('Total cost of the transaction'),

                // Created At
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->tooltip('Date and time when the transaction was created'),
            ])
            ->filters([
                // Filter by Payment Status
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'pending' => 'Pending',
                        'success' => 'Success',
                        'failed' => 'Failed',
                        'complete' => 'Complete', // Gunakan 'complete' sesuai ENUM di database
                    ])
                    ->placeholder('All Statuses'),
            ])
            ->actions([
                // Approve Action
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn(Transaction $record): bool => $record->payment_status === 'pending')
                    ->action(function (Transaction $record): void {
                        $record->update(['payment_status' => 'complete']); // Gunakan 'complete' sesuai ENUM di database
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Approve Transaction')
                    ->modalDescription('Are you sure you want to approve this transaction? This action cannot be undone.')
                    ->modalSubmitActionLabel('Approve'),

                // Mark as Pending Action
                Tables\Actions\Action::make('markPending')
                    ->label('Mark as Pending')
                    ->color('warning')
                    ->icon('heroicon-o-clock')
                    ->visible(fn(Transaction $record): bool => $record->payment_status !== 'pending')
                    ->action(function (Transaction $record): void {
                        $record->update(['payment_status' => 'pending']);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Mark Transaction as Pending')
                    ->modalDescription('Are you sure you want to mark this transaction as pending?')
                    ->modalSubmitActionLabel('Mark as Pending'),

                // Mark as Failed Action
                Tables\Actions\Action::make('markFailed')
                    ->label('Mark as Failed')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn(Transaction $record): bool => $record->payment_status !== 'failed')
                    ->action(function (Transaction $record): void {
                        $record->update(['payment_status' => 'failed']);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Mark Transaction as Failed')
                    ->modalDescription('Are you sure you want to mark this transaction as failed?')
                    ->modalSubmitActionLabel('Mark as Failed'),
            ]);
    }
}