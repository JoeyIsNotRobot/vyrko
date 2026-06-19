@extends('layouts.app')

@section('content')
    @php($en = app()->getLocale() === 'en')

    <x-career.resource-edit-form
        :eyebrow="$en ? 'Education' : 'Formação'"
        :title="$en ? 'Edit education' : 'Editar formação'"
        :subtitle="$en ? 'Keep academic background concise and relevant to target roles.' : 'Mantenha a formação concisa e relevante para os cargos alvo.'"
        :action="route('educations.update', $education)"
    >
        <div class="form-row">
            <div class="field">
                <label>{{ $en ? 'Institution' : 'Instituição' }}</label>
                <input name="institution" value="{{ old('institution', $education->institution) }}" placeholder="{{ $en ? 'University or school' : 'Universidade ou escola' }}" required>
            </div>
            <div class="field">
                <label>{{ $en ? 'Degree' : 'Grau' }}</label>
                <input name="degree" value="{{ old('degree', $education->degree) }}" placeholder="{{ $en ? 'Bachelor, MBA, Bootcamp...' : 'Bacharelado, MBA, Bootcamp...' }}" required>
            </div>
        </div>
        <div class="field">
            <label>{{ $en ? 'Field of study' : 'Área' }}</label>
            <input name="field_of_study" value="{{ old('field_of_study', $education->field_of_study) }}" placeholder="{{ $en ? 'Computer Science, Systems Analysis...' : 'Ciência da Computação, Análise de Sistemas...' }}">
        </div>
        <div class="field">
            <label>{{ $en ? 'Description' : 'Descrição' }}</label>
            <textarea class="compact-textarea" name="description" placeholder="{{ $en ? 'Relevant coursework, thesis, honors or focus areas...' : 'Disciplinas relevantes, TCC, honras ou áreas de foco...' }}">{{ old('description', $education->description) }}</textarea>
        </div>
    </x-career.resource-edit-form>
@endsection
