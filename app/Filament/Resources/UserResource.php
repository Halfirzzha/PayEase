<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationGroup = 'Users';

    /**
     * Define the form schema for creating and editing users.
     */
    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('User Details')
                ->columns([
                    'sm' => 3,
                    'xl' => 6,
                    '2xl' => 8,
                ])
                ->schema([
                    // Full Name
                    TextInput::make('name')
                        ->label('Full Name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Enter full name')
                        ->helperText('The full name of the user.')
                        ->columnSpan([
                            'sm' => 2,
                            'xl' => 3,
                            '2xl' => 4,
                        ]),

                    // Email Address
                    TextInput::make('email')
                        ->label('Email Address')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true) // Ensure uniqueness
                        ->maxLength(255)
                        ->placeholder('Enter email address')
                        ->helperText('The email address of the user (must be unique).')
                        ->columnSpan([
                            'sm' => 2,
                            'xl' => 3,
                            '2xl' => 4,
                        ]),

                    // Password
                    TextInput::make('password')
                        ->label('Password')
                        ->password()
                        ->required()
                        ->revealable()
                        ->rules(['regex:/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*?&]{4,}$/']) // Minimum 4 characters, at least one letter and one number
                        ->maxLength(255)
                        ->placeholder('Enter password')
                        ->helperText('Password must be at least 4 characters long and include letters and numbers.')
                        ->columnSpan([
                            'sm' => 2,
                            'xl' => 3,
                            '2xl' => 4,
                        ]),

                    // Phone Number
                    TextInput::make('phone')
                        ->label('Phone Number')
                        ->tel()
                        ->maxLength(15)
                        ->unique(ignoreRecord: true) // Ensure uniqueness
                        ->placeholder('Enter phone number (e.g., +628123456789)')
                        ->rules(['regex:/^\+?[0-9]{6,15}$/']) // Validasi nomor telepon
                        ->helperText('The phone number of the user (must be unique).')
                        ->columnSpan([
                            'sm' => 2,
                            'xl' => 3,
                            '2xl' => 4,
                        ]),

                    // Roles
                    Forms\Components\Select::make('roles')
                        ->label('Roles')
                        ->multiple() // Allow multiple role selection
                        ->relationship('roles', 'name') // Define the relationship with roles
                        ->placeholder('Select roles for the user')
                        ->helperText('Assign one or more roles to the user.')
                        ->columnSpan([
                            'sm' => 2,
                            'xl' => 3,
                            '2xl' => 4,
                        ]),
                ]),

            Section::make('Uploads')
                ->columns([
                    'sm' => 3,
                    'xl' => 6,
                    '2xl' => 8,
                ])
                ->schema([
                    // Profile Picture
                    Forms\Components\FileUpload::make('photo')
                        ->label('Profile Picture')
                        ->image()
                        ->maxSize(2048) // Maksimal ukuran file 2MB
                        ->directory('user_photos') // Direktori penyimpanan
                        ->helperText('Upload a profile picture (max size: 2MB).')
                        ->columnSpan([
                            'sm' => 1,
                            'xl' => 3,
                            '2xl' => 4,
                        ]),

                    // Scan Sertifikat
                    Forms\Components\FileUpload::make('scan_certificate')
                        ->label('Scan Sertifikat')
                        ->image()
                        ->maxSize(2048) // Maksimal ukuran file 2MB
                        ->directory('user_certificates') // Direktori penyimpanan
                        ->helperText('Upload a scanned certificate (max size: 2MB).')
                        ->columnSpan([
                            'sm' => 1,
                            'xl' => 3,
                            '2xl' => 4,
                        ]),
                ]),
        ]);
    }

    /**
     * Define the table schema for listing users.
     */
    public static function table(Table $table): Table
    {
        return $table->columns([
            // Full Name
            Tables\Columns\TextColumn::make('name')
                ->label('Full Name')
                ->searchable(),

            // Email Address
            Tables\Columns\TextColumn::make('email')
                ->label('Email Address')
                ->searchable(),
            
            // // Roles
            Tables\Columns\TextColumn::make('roles.name')
                ->label('Roles'),

            // Phone Number
            Tables\Columns\TextColumn::make('phone')
                ->label('Phone Number')
                ->searchable(),

            // Profile Picture
            Tables\Columns\ImageColumn::make('photo')
                ->label('Profile Picture'),

            // Scan Sertifikat
            Tables\Columns\ImageColumn::make('scan_certificate')
                ->label('Scan Sertifikat'),

            // Created At
            Tables\Columns\TextColumn::make('created_at')
                ->label('Created At')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            // Updated At
            Tables\Columns\TextColumn::make('updated_at')
                ->label('Updated At')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make()->after(function (Collection $records) {
                foreach ($records as $record) {
                    // Delete the user's profile picture if it exists
                    if ($record->photo) {
                        Storage::disk('public')->delete($record->photo);
                    }

                    // Delete the user's scanned certificate if it exists
                    if ($record->scan_sertifikat) {
                        Storage::disk('public')->delete($record->scan_sertifikat);
                    }
                }
            }),
        ]);
    }

    /**
     * Define the relations for the user resource.
     */
    public static function getRelations(): array
    {
        return [];
    }

    /**
     * Define the pages for the user resource.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}