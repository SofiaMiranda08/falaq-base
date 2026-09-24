@extends('layouts.app')

@section('title', 'Criar Evento — FalaQ')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow-md text-gray-900">
    <h2 class="text-2xl font-bold mb-6">Criar Novo Evento</h2>

    <form action="{{ route('eventos.store') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label for="titulo" class="block font-semibold mb-2">Título</label>

            <input
                type="text"
                name="titulo"
                id="titulo"
                value="{{ old('titulo') }}"
                class="w-full border border-gray-300 rounded-md p-2 @error('titulo') border-red-500 @enderror"
                placeholder="Digite o título do evento"
            >

            @error('titulo')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="descricao" class="block font-semibold mb-2">Descrição</label>

            <textarea
                name="descricao"
                id="descricao"
                rows="5"
                class="w-full border border-gray-300 rounded-md p-2 @error('descricao') border-red-500 @enderror"
                placeholder="Digite a descrição do evento"
            >{{ old('descricao') }}</textarea>

            @error('descricao')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button
            type="submit"
            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
        >
            Criar Evento
        </button>
    </form>
</div>
@endsection
