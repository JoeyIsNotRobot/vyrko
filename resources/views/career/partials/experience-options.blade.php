<option value="">{{ app()->getLocale() === 'en' ? 'No link' : 'Sem vínculo' }}</option>
@foreach ($experiences as $experience)
    <option value="{{ $experience->id }}">{{ $experience->role_title }} - {{ $experience->company_name }}</option>
@endforeach
