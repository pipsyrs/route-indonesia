<?php

namespace Tests\Feature;

use App\Livewire\Pages\Payment;
use App\Services\PaymentAccountService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\Livewire;
use Tests\TestCase;

/** Transfer manual: daftar rekening tujuan + upload bukti pembayaran (belum disimpan permanen). */
class PaymentProofUploadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_it_should_expose_active_bank_accounts_via_computed_not_public_state(): void
    {
        config(['payment.bank_accounts' => [
            ['bank' => 'BCA', 'number' => '1234567890', 'holder' => 'PT Demo', 'active' => true],
            ['bank' => 'BNI', 'number' => '999', 'holder' => 'PT Demo', 'active' => false],
            ['bank' => '', 'number' => '1', 'holder' => 'x'],
        ]]);

        $expected = [['bank' => 'BCA', 'number' => '1234567890', 'holder' => 'PT Demo']];
        $this->assertSame($expected, app(PaymentAccountService::class)->active());

        $component = Livewire::test(Payment::class);
        $this->assertSame($expected, $component->instance()->bankAccounts);
        $this->assertArrayNotHasKey('bankAccounts', $component->instance()->all());
    }

    public function test_it_should_ship_two_obviously_fake_default_accounts(): void
    {
        $accounts = app(PaymentAccountService::class)->active();

        $this->assertCount(2, $accounts);
        foreach ($accounts as $account) {
            $this->assertSame(['bank', 'number', 'holder'], array_keys($account));
            $this->assertStringContainsString('Demo', $account['holder']);
        }
    }

    public function test_it_should_require_proof_file(): void
    {
        Livewire::test(Payment::class)
            ->call('submit')
            ->assertHasErrors(['form.proof' => 'required'])
            ->assertNotDispatched('payment-proof-submitted');
    }

    public function test_it_should_reject_disallowed_mime_type(): void
    {
        Livewire::test(Payment::class)
            ->set('form.proof', UploadedFile::fake()->create('proof.exe', 10, 'application/x-msdownload'))
            ->call('submit')
            ->assertHasErrors('form.proof')
            ->assertSet('form.proof', null)
            ->assertNotDispatched('payment-proof-submitted');
    }

    public function test_it_should_show_indonesian_messages_at_temporary_upload_stage(): void
    {
        Livewire::test(Payment::class)
            ->set('form.proof', UploadedFile::fake()->create('proof.exe', 10, 'application/x-msdownload'))
            ->assertHasErrors('form.proof')
            ->assertSee('File harus berformat JPG, PNG, atau PDF.', false)
            ->assertDontSee('must be a file of type', false);

        // Validator yang sama dengan FileUploadController Livewire, di kedua locale.
        foreach (['id', 'en'] as $locale) {
            app()->setLocale($locale);

            $cases = [
                'Ukuran file maksimal 2 MB.' => UploadedFile::fake()->create('proof.pdf', 1500, 'application/pdf'),
                'File harus berformat JPG, PNG, atau PDF.' => UploadedFile::fake()->create('proof.txt', 1, 'text/plain'),
                'Unggahan harus berupa file.' => 'not-a-file',
            ];

            foreach ($cases as $message => $file) {
                // Fake file > upload_max_filesize php.ini (2M) dianggap invalid, jadi pemetaan pesan max diuji dengan batas kecil.
                $rules = str_starts_with($message, 'Ukuran') ? ['max:1024'] : FileUploadConfiguration::rules();
                $errors = Validator::make(['files' => [$file]], ['files.*' => $rules])->errors();
                $this->assertContains($message, $errors->get('files.0'), "{$locale}: {$message}");
            }
        }
    }

    public function test_it_should_reject_string_proof_with_validation_error_not_500(): void
    {
        Livewire::test(Payment::class)
            ->set('form.proof', 'livewire-file:../../.env')
            ->assertHasErrors('form.proof')
            ->assertSet('form.proof', null)
            ->assertSee('Bukti pembayaran harus berupa file.', false)
            ->call('submit')
            ->assertHasErrors(['form.proof' => 'required'])
            ->assertNotDispatched('payment-proof-submitted');
    }

    public function test_it_should_delete_temporary_file_after_successful_submit(): void
    {
        $component = Livewire::test(Payment::class)
            ->set('form.proof', UploadedFile::fake()->image('proof.jpg'));

        $tmpFiles = fn () => FileUploadConfiguration::storage()->allFiles(FileUploadConfiguration::path());
        $this->assertNotEmpty($tmpFiles());

        $component->call('submit')->assertDispatched('payment-proof-submitted');

        $this->assertSame([], $tmpFiles());
    }

    public function test_it_should_reject_svg_disguised_as_image(): void
    {
        Livewire::test(Payment::class)
            ->set('form.proof', UploadedFile::fake()->createWithContent('proof.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'))
            ->call('submit')
            ->assertHasErrors('form.proof')
            ->assertNotDispatched('payment-proof-submitted');
    }

    public function test_it_should_reject_file_larger_than_two_megabytes(): void
    {
        Livewire::test(Payment::class)
            ->set('form.proof', UploadedFile::fake()->image('proof.jpg')->size(2049))
            ->call('submit')
            ->assertHasErrors('form.proof')
            ->assertNotDispatched('payment-proof-submitted');
    }

    public function test_it_should_reject_sender_name_over_100_chars(): void
    {
        Livewire::test(Payment::class)
            ->set('form.proof', UploadedFile::fake()->image('proof.png'))
            ->set('form.senderName', str_repeat('a', 101))
            ->call('submit')
            ->assertHasErrors(['form.senderName' => 'max']);
    }

    public function test_it_should_show_indonesian_validation_message(): void
    {
        Livewire::test(Payment::class)
            ->call('submit')
            ->assertSee('Bukti pembayaran wajib diunggah.', false);
    }

    public function test_it_should_dispatch_event_and_reset_form_on_valid_submit(): void
    {
        foreach (['proof.jpg', 'proof.png'] as $name) {
            Livewire::test(Payment::class)
                ->set('form.proof', UploadedFile::fake()->image($name)->size(500))
                ->set('form.senderName', 'Budi')
                ->call('submit')
                ->assertHasNoErrors()
                ->assertDispatched('payment-proof-submitted')
                ->assertSet('form.proof', null)
                ->assertSet('form.senderName', '')
                ->assertNoRedirect();
        }

        Livewire::test(Payment::class)
            ->set('form.proof', UploadedFile::fake()->create('proof.pdf', 100, 'application/pdf'))
            ->call('submit')
            ->assertHasNoErrors()
            ->assertDispatched('payment-proof-submitted');
    }

    public function test_it_should_rate_limit_submit_attempts(): void
    {
        $component = Livewire::test(Payment::class);

        for ($i = 0; $i < Payment::SUBMIT_MAX_ATTEMPTS; $i++) {
            $component->call('submit')->assertHasErrors(['form.proof' => 'required']);
        }

        $component
            ->set('form.proof', UploadedFile::fake()->image('proof.jpg'))
            ->call('submit')
            ->assertHasErrors('form.proof')
            ->assertSee('Terlalu banyak percobaan', false)
            ->assertNotDispatched('payment-proof-submitted');
    }

    public function test_it_should_store_temporary_upload_on_private_disk(): void
    {
        $this->assertSame('local', config('livewire.temporary_file_upload.disk'));
        $this->assertStringNotContainsString('public', config('filesystems.disks.local.root'));
        $this->assertNotEmpty(config('livewire.temporary_file_upload.middleware'));
    }
}
