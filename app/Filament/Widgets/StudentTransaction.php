<?php

namespace App\Filament\Widgets;

use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class StudentTransaction extends BaseWidget
{
    use HasWidgetShield;

    protected static ?string $heading = 'Transaction History';
    protected int | string | array $columnSpan = 'full';

    /**
     * Define the table schema for the widget.
     */
    public function table(Table $table): Table
    {
        $user = Auth::user(); // Mendapatkan pengguna yang sedang login

        return $table
            ->query(
                Transaction::query()
                    ->where('user_id', $user->id) // Filter transaksi berdasarkan pengguna yang sedang login
                    ->with(['user', 'department']) // Eager load relasi untuk mengurangi query N+1
                    ->orderBy('created_at', 'DESC') // Urutkan berdasarkan transaksi terbaru
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
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'success' => 'success',
                        'failed' => 'danger',
                        'complete' => 'primary',
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
            ->actions([
                // Payment Action
                Tables\Actions\Action::make('Payment')
                    ->label('Payment')
                    ->icon('heroicon-o-credit-card')
                    ->url(fn ($record) => url("payflow/payment/{$record->id}")) // Redirect to payment page with transaction ID
                    ->visible(fn ($record) => $record->payment_status === 'pending') // Show only if payment_status is 'pending'
                    ->tooltip('Proceed to payment for this transaction'),
            ]);
    }
}