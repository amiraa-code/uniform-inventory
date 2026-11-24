@extends('layouts.app')

@section('title', 'Scan Label')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Scan Item Label</h1>
        <p class="mt-1 text-sm text-slate-500">Point the camera at a uniform's QR label or barcode to open it.</p>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2" data-scanner data-lookup-url="{{ route('items.lookup') }}">
        <section class="card p-5">
            {{-- The camera picture is shown inside this box by the html5-qrcode library. --}}
            <div id="scanner-view" class="mx-auto aspect-square w-full max-w-sm overflow-hidden rounded-md bg-slate-900"></div>

            <p data-scanner-status aria-live="polite" class="mt-3 text-center text-sm text-slate-500">Camera is off.</p>

            <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:justify-center">
                <button type="button" data-scanner-start class="btn-accent">📷 Start camera</button>
                <button type="button" data-scanner-stop class="btn-secondary hidden">Stop camera</button>
            </div>
        </section>

        <section class="card p-5">
            <h2 class="font-semibold text-slate-900">No camera?</h2>

            {{-- On phones, capture="environment" opens the back camera to take a photo of the label. --}}
            <label for="scan-photo" class="form-label mt-4">Scan from a photo</label>
            <input type="file" id="scan-photo" accept="image/*" capture="environment" data-scanner-file
                   class="mt-1 block w-full text-sm text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white">

            <form data-scanner-manual class="mt-6">
                <label for="manual-sku" class="form-label">Or type the SKU</label>
                <div class="mt-1 flex gap-2">
                    <input type="text" id="manual-sku" required placeholder="e.g. PPE-BOOT-UK9" class="form-input mt-0 uppercase" autocomplete="off">
                    <button type="submit" class="btn-primary">Find</button>
                </div>
            </form>
        </section>
    </div>
@endsection
