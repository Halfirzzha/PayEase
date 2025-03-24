<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use App\Models\Department;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static ?string $navigationGroup = 'Operations';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Transaction Details')
                    ->columns([
                        'sm' => 3,
                        'xl' => 6,
                        '2xl' => 8,
                    ])
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->default(fn (): string => 'TRX-' . now()->format('Ymd') . '-' . str_pad(mt_rand(0, 9999999), 7, '0', STR_PAD_LEFT))
                            ->columnSpan([
                                'sm' => 1,
                                'xl' => 2,
                                '2xl' => 2,
                            ]),
                        Forms\Components\Select::make('user_id')
                            ->required()
                            ->relationship('user', 'name') // Menggunakan relasi 'user' sesuai model
                            ->searchable()
                            ->columnSpan([
                                'sm' => 1,
                                'xl' => 2,
                                '2xl' => 2,
                            ]),
                        Forms\Components\TextInput::make('payment_status')
                            ->readOnly()
                            ->default('pending')
                            ->columnSpan([
                                'sm' => 1,
                                'xl' => 2,
                                '2xl' => 2,
                            ]),
                        Forms\Components\Select::make('payment_method')
                            ->label('Payment Method')
                            ->required()
                            ->options([
                                'cash' => 'Cash',
                                'transfer' => 'Transfer',
                                'digital_wallet' => 'Digital Wallet',
                            ])
                            ->columnSpan([
                                'sm' => 1,
                                'xl' => 2,
                                '2xl' => 2,
                            ]),
                        Forms\Components\Fieldset::make('Department')
                            ->schema([
                                Forms\Components\Select::make('department_id')
                                    ->required()
                                    ->label('Department Name & Semester')
                                    ->options(Department::all()->mapWithKeys(function ($department) {
                                        return [$department->id => $department->name . ' - Semester ' . $department->semester];
                                    })->toArray())
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $department = Department::find($state);
                                        $set('department_cost', $department?->cost ?? null);
                                    })
                                    ->columnSpan([
                                        'sm' => 2,
                                        'xl' => 3,
                                        '2xl' => 4,
                                    ]),
                                Forms\Components\TextInput::make('department_cost')
                                    ->label('Cost')
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->columnSpan([
                                        'sm' => 1,
                                        'xl' => 2,
                                        '2xl' => 2,
                                    ]),
                            ])
                            ->columnSpan([
                                'sm' => 3,
                                'xl' => 6,
                                '2xl' => 8,
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name') // Menggunakan relasi 'user'
                    ->label('User Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.phone') // Menggunakan relasi 'user'
                    ->label('User Phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'success' => 'success',
                        'failed' => 'danger',
                        'complete' => 'primary',
                        default => 'secondary',
                    }),
                Tables\Columns\ImageColumn::make('payment_proof')
                    ->label('Payment Proof')
                    ->width(450)
                    ->height(225),
                Tables\Columns\TextColumn::make('department.name') // Menggunakan relasi 'department'
                    ->label('Department')
                    ->searchable(),
                Tables\Columns\TextColumn::make('department.semester') // Menggunakan relasi 'department'
                    ->label('Semester')
                    ->searchable(),
                Tables\Columns\TextColumn::make('department.cost') // Menggunakan relasi 'department'
                    ->label('Cost')
                    ->money('IDR') // Format as currency
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'success' => 'Success',
                        'failed' => 'Failed',
                        'complete' => 'Complete',
                    ])
                    ->label('Payment Status'),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn(Transaction $record): bool => $record->payment_status === 'pending')
                    ->action(function (Transaction $record): void {
                        $record->update(['payment_status' => 'complete']);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Approve Transaction')
                    ->modalDescription('Are you sure you want to approve this transaction? This action cannot be undone.')
                    ->modalSubmitActionLabel('Approve'),
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
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Define relations here if needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}