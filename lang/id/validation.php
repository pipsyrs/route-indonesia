<?php

// Hanya pesan upload temporary Livewire (atribut "files.*"); sisanya pakai bawaan framework.
return [
    'custom' => [
        'files.*' => [
            // Urutan penting: wildcard "files.*.file" juga cocok dengan "files.0.max.file", jadi "file" ditaruh terakhir.
            'required' => 'File wajib dipilih.',
            'max' => ['file' => 'Ukuran file maksimal 2 MB.'],
            'mimes' => 'File harus berformat JPG, PNG, atau PDF.',
            'uploaded' => 'File gagal diunggah. Coba lagi.',
            'file' => 'Unggahan harus berupa file.',
        ],
    ],
];
