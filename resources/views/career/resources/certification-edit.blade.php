@extends('layouts.app')

@section('content')
    @php($en = app()->getLocale() === 'en')

    <x-career.resource-edit-form
        :eyebrow="$en ? 'Certification' : 'Certificação'"
        :title="$en ? 'Edit certification' : 'Editar certificação'"
        :subtitle="$en ? 'Keep credentials, issuers and validation links ready for recruiter review.' : 'Mantenha credenciais, emissores e links de validação prontos para revisão por recrutadores.'"
        :action="route('certifications.update', $certification)"
    >
        <div class="form-row">
            <div class="field">
                <label>{{ $en ? 'Name' : 'Nome' }}</label>
                <input name="name" value="{{ old('name', $certification->name) }}" placeholder="AWS Certified Cloud Practitioner" required>
            </div>
            <div class="field">
                <label>{{ $en ? 'Issuer' : 'Emissor' }}</label>
                <input name="issuer" value="{{ old('issuer', $certification->issuer) }}" placeholder="AWS">
            </div>
        </div>
        <div class="form-row">
            <div class="field">
                <label>{{ $en ? 'Issued at' : 'Emitida em' }}</label>
                <input name="issued_at" type="date" value="{{ old('issued_at', optional($certification->issued_at)->format('Y-m-d')) }}">
            </div>
            <div class="field">
                <label>{{ $en ? 'Expires at' : 'Expira em' }}</label>
                <input name="expires_at" type="date" value="{{ old('expires_at', optional($certification->expires_at)->format('Y-m-d')) }}">
            </div>
        </div>
        <div class="field">
            <label>Credential URL</label>
            <input name="credential_url" value="{{ old('credential_url', $certification->credential_url) }}" placeholder="https://...">
            <p class="form-help">{{ $en ? 'Use a public validation link when available.' : 'Use um link público de validação quando disponível.' }}</p>
        </div>
        <div class="field">
            <label>{{ $en ? 'Description' : 'Descrição' }}</label>
            <textarea class="compact-textarea" name="description" placeholder="{{ $en ? 'Add scope, relevant topics or validation context...' : 'Adicione escopo, tópicos relevantes ou contexto de validação...' }}">{{ old('description', $certification->description) }}</textarea>
        </div>
    </x-career.resource-edit-form>
@endsection
