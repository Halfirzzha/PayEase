<?php

namespace App\Filament\Pages;

use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Section;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class Biodata extends Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Users';
    protected static string $view = 'filament.pages.biodata';

    public $user;
    public ?array $data = [];

    /**
     * Mount the page and initialize the form with current user data.
     */
    public function mount(): void
    {
        $this->user = Auth::user();

        // Initialize form with current user data
        $this->form->fill([
            'name' => $this->user->name,
            'email' => $this->user->email,
            'phone' => $this->user->phone,
            'photo' => $this->user->photo,
            'scan_certificate' => $this->user->scan_certificate,
        ]);
    }

    /**
     * Define the form schema for the biodata page.
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Personal Information')
                    ->columns([
                        'sm' => 3,
                        'xl' => 6,
                        '2xl' => 8,
                    ])
                    ->schema([
                        TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter your full name')
                            ->columnSpan([
                                'sm' => 2,
                                'xl' => 3,
                                '2xl' => 4,
                            ]),
                        TextInput::make('email')
                            ->label('Email Address')
                            ->required()
                            ->email()
                            ->maxLength(255)
                            ->placeholder('Enter your email address')
                            ->columnSpan([
                                'sm' => 2,
                                'xl' => 3,
                                '2xl' => 4,
                            ]),
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->nullable()
                            ->minLength(8)
                            ->placeholder('Enter a new password (optional)')
                            ->columnSpan([
                                'sm' => 2,
                                'xl' => 3,
                                '2xl' => 4,
                            ]),
                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->required()
                            ->rules(['regex:/^\+?[0-9]{6,15}$/']) // Validasi nomor telepon (6-15 digit angka)
                            ->placeholder('Enter a valid phone number (e.g., +628123456789)')
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
                        FileUpload::make('photo')
                            ->label('Profile Picture')
                            ->image()
                            ->maxSize(2048)
                            ->nullable()
                            ->directory('user_photos')
                            ->placeholder('Upload your profile picture')
                            ->columnSpan([
                                'sm' => 1,
                                'xl' => 3,
                                '2xl' => 4,
                            ]),
                        FileUpload::make('scan_certificate')
                            ->label('Certificate Scan')
                            ->image()
                            ->maxSize(2048)
                            ->nullable()
                            ->directory('user_certificates')
                            ->placeholder('Upload your last certificate')
                            ->columnSpan([
                                'sm' => 1,
                                'xl' => 3,
                                '2xl' => 4,
                            ]),
                    ]),
            ])->statePath('data');
    }

    /**
     * Handle the form submission and update user biodata.
     */
    public function edit(): void
    {
        // Validate form data
        $validatedData = $this->form->getState();

        // Update the user's details
        $this->updateUserDetails($validatedData);

        // Handle file uploads
        $this->handleFileUploads($validatedData);

        // Save the updated user data
        $this->user->save();

        // Send a success notification
        Notification::make()
            ->title('Biodata Updated')
            ->success()
            ->body('Your biodata has been successfully updated.')
            ->send();
    }

    /**
     * Update user details (name, email, phone, password).
     */
    private function updateUserDetails(array $data): void
    {
        $this->user->name = strip_tags($data['name']);
        $this->user->email = strip_tags($data['email']);
        $this->user->phone = strip_tags($data['phone']);

        // Update password if provided
        if (!empty($data['password'])) {
            $this->user->password = Hash::make($data['password']);
        }
    }

    /**
     * Handle file uploads (photo and certificate scan).
     */
    private function handleFileUploads(array $data): void
    {
        // Handle photo upload
        if (isset($data['photo'])) {
            if ($this->user->photo) {
                Storage::delete($this->user->photo);
            }
            $this->user->photo = $data['photo'];
        }

        // Handle certificate scan upload
        if (isset($data['scan_certificate'])) {
            if ($this->user->scan_certificate) {
                Storage::delete($this->user->scan_certificate);
            }
            $this->user->scan_certificate = $data['scan_certificate'];
        }
    }
}