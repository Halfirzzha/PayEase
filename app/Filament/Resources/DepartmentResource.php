<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentResource\Pages;
use App\Models\Department;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;
    public static function getNavigationBadge(): ?string
        {
            return static::getModel()::count();
        }
    protected static ?string $navigationGroup = 'Operations';
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    /**
     * Define the form schema for creating and editing departments.
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Department Details')
                    ->columns([
                        'sm' => 3,
                        'xl' => 6,
                        '2xl' => 8,
                    ])
                    ->schema([
                        // Department Name
                        TextInput::make('name')
                            ->label('Department Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter department name')
                            ->helperText('The name of the department (e.g., Computer Science).')
                            ->columnSpan([
                                'sm' => 2,
                                'xl' => 3,
                                '2xl' => 4,
                            ]),

                        // Semester
                        TextInput::make('semester')
                            ->label('Semester')
                            ->required()
                            ->numeric()
                            ->minValue(1) // Ensure positive numbers
                            ->placeholder('Enter semester (e.g., 1, 2, 3)')
                            ->helperText('The semester number (must be a positive integer).')
                            ->columnSpan([
                                'sm' => 1,
                                'xl' => 2,
                                '2xl' => 2,
                            ]),

                        // Cost
                        TextInput::make('cost')
                            ->label('Cost')
                            ->required()
                            ->numeric()
                            ->minValue(0) // Ensure non-negative numbers
                            ->prefix('Rp')
                            ->placeholder('Enter cost (e.g., 500000)')
                            ->helperText('The cost associated with the department (in IDR).')
                            ->columnSpan([
                                'sm' => 1,
                                'xl' => 2,
                                '2xl' => 2,
                            ]),
                    ]),
            ]);
    }

    /**
     * Define the table schema for listing departments.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Department Name
                Tables\Columns\TextColumn::make('name')
                    ->label('Department Name')
                    ->searchable()
                    ->tooltip('The name of the department.'),

                // Semester
                Tables\Columns\TextColumn::make('semester')
                    ->label('Semester')
                    ->numeric()
                    ->sortable()
                    ->tooltip('The semester number.'),

                // Cost
                Tables\Columns\TextColumn::make('cost')
                    ->label('Cost')
                    ->money('IDR') // Format as currency
                    ->sortable()
                    ->tooltip('The cost associated with the department.'),

                // Created At
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip('The date and time when the department was created.'),

                // Updated At
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip('The date and time when the department was last updated.'),
            ])
            ->filters([
                // Add filters here if needed
            ])
            ->actions([
                Tables\Actions\EditAction::make(), // Edit action
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(), // Bulk delete action
                ]),
            ]);
    }

    /**
     * Define the relations for the department resource.
     */
    public static function getRelations(): array
    {
        return [
            // Define relations here if needed
        ];
    }

    /**
     * Define the pages for the department resource.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            'edit' => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }
}