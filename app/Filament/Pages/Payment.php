<?php

namespace App\Filament\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use App\Models\Transaction;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class Payment extends Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.payment';

    public $transaction;
    public ?array $data = []; // Properti untuk menampung data formulir

    public static function shouldRegisterNavigation(): bool
    {
        return false; // Menyembunyikan dari sidebar
    }

    public function mount(int $id): void
    {
        // Ambil transaksi berdasarkan ID
        $this->transaction = Transaction::findOrFail($id);

        // Isi data awal formulir berdasarkan transaksi
        $this->data = [
            'payment_method' => $this->transaction->payment_method ?? null,
            'payment_proof' => $this->transaction->payment_proof ?? null,
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->options([
                        'cash' => 'Cash',
                        'transfer' => 'Transfer',
                        'digital_wallet' => 'Digital Wallet',
                    ])
                    ->required()
                    ->default($this->data['payment_method']), // Menggunakan data awal

                FileUpload::make('payment_proof')
                    ->label('Bukti Pembayaran')
                    ->image()
                    ->required()
                    ->directory('payment_proofs') // Menentukan direktori penyimpanan
                    ->maxSize(2048) // Maksimal ukuran file 2MB
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg']) // Validasi tipe file
                    ->columnSpanFull(),
            ])->statePath('data'); // Mengikat data ke properti $data
    }

    public function edit()
    {
        // Validasi data menggunakan Validator
        $validatedData = $this->validateFormData($this->form->getState());

        // Hapus file lama jika file baru diunggah
        $this->deleteOldFileIfNeeded($validatedData['payment_proof']);

        // Update transaksi
        $this->transaction->update([
            'payment_method' => $validatedData['payment_method'],
            'payment_proof' => $validatedData['payment_proof']
        ]);

        // Kirim notifikasi
        Notification::make()
            ->title('Pembayaran Berhasil!')
            ->body('Terima Kasih Telah Membayar. Mohon Tunggu Persetujuan Oleh Admin.')
            ->success()
            ->send();

        // Redirect ke halaman admin
        return redirect('/payflow');
    }

    /**
     * Validasi data formulir.
     *
     * @param array $data
     * @return array
     */
    private function validateFormData(array $data): array
    {
        return Validator::make($data, [
            'payment_method' => 'required|in:cash,transfer,digital_wallet',
            'payment_proof' => 'required|string|max:255',
        ])->validate();
    }

    /**
     * Hapus file lama jika file baru diunggah.
     *
     * @param string|null $newFile
     * @return void
     */
    private function deleteOldFileIfNeeded(?string $newFile): void
    {
        if ($newFile && $newFile !== $this->transaction->payment_proof) {
            if ($this->transaction->payment_proof) {
                Storage::delete($this->transaction->payment_proof);
            }
        }
    }
}