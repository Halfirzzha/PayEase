<?php

namespace App\Filament\Auth;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Filament\Pages\Auth\Register as AuthRegister;

class Register extends AuthRegister
{
    /**
     * Define the form schema for the registration page.
     *
     * @return array
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNameFormComponent()
                            ->placeholder('Enter your full name'),
                        $this->getEmailFormComponent()
                            ->placeholder('Enter your email address'),
                        $this->getPasswordFormComponent()
                            ->placeholder('Enter your password'),
                        $this->getPasswordConfirmationFormComponent()
                            ->placeholder('Confirm your password'),
                        TextInput::make('phone')
                            ->tel()
                            ->required()
                            ->label('Phone Number')
                            ->placeholder('Enter your phone number (e.g., +628123456789)')
                            ->rules(['required', 'regex:/^\+?[0-9]{6,15}$/', 'unique:users,phone'])
                            ->columnSpanFull(),
                        FileUpload::make('photo')
                            ->label('Profile Picture')
                            ->columnSpanFull()
                            ->image()
                            ->directory('user_photos') // Direktori penyimpanan
                            ->placeholder('Upload your profile picture')
                            ->rules(['nullable', 'image', 'max:2048']),
                        FileUpload::make('scan_certificate')
                            ->label('Scan of Certificate')
                            ->columnSpanFull()
                            ->image()
                            ->directory('user_certificates') // Direktori penyimpanan
                            ->placeholder('Upload your last certificate')
                            ->rules(['nullable', 'image', 'max:2048']),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    /**
     * Handle the form submission for user registration.
     *
     * @return void
     */
    protected function submit(): void
    {
        // Show loading notification
        Notification::make()
            ->title('Processing Registration')
            ->description('Please wait while we process your registration...')
            ->icon('heroicon-o-clock')
            ->send();

        // Retrieve the form state (user input)
        $data = $this->form->getState();

        // Validate the data before creating the user
        $validatedData = $this->validateRegistrationData($data);

        // Hash the password for security
        $validatedData['password'] = Hash::make($validatedData['password']);

        // Create a new user
        $user = $this->createUser($validatedData);

        // Log the user in after successful registration
        Auth::login($user);

        // Show success notification
        Notification::make()
            ->title('Registration Successful')
            ->description('Welcome! Your account has been created successfully.')
            ->success()
            ->send();
    }

    /**
     * Validate the registration data.
     *
     * @param array $data
     * @return array
     */
    private function validateRegistrationData(array $data): array
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
            'phone' => ['required', 'regex:/^\+?[0-9]{6,15}$/', 'unique:users,phone'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'scan_certificate' => ['nullable', 'image', 'max:2048'],
        ])->validate();
    }

    /**
     * Create a new user with the validated data.
     *
     * @param array $validatedData
     * @return User
     */
    private function createUser(array $validatedData): User
    {
        return User::create([
            'name' => strip_tags($validatedData['name']),
            'email' => strip_tags($validatedData['email']),
            'password' => $validatedData['password'],
            'phone' => $validatedData['phone'],
            'photo' => $validatedData['photo'] ?? null,
            'scan_certificate' => $validatedData['scan_certificate'] ?? null,
        ]);
    }
}