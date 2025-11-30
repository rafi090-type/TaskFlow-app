@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">

                <h2 class="text-2xl font-bold mb-6">Daftar Tugas Saya</h2>

                <!-- Form Tambah Tugas -->
                <form method="POST" action="{{ route('tasks.store') }}" class="mb-8">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="md:col-span-2">
                            <input type="text" name="title" placeholder="Judul tugas..." required
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                        <div>
                            <input type="date" name="deadline"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                        <div>
                            <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                                Tambah
                            </button>
                        </div>
                    </div>
                    <div class="mt-3">
                        <textarea name="description" placeholder="Deskripsi (opsional)"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
                    </div>
                </form>

                <!-- Pesan Sukses -->
                @if(session('success'))
                    <div class="mb-6 px-4 py-3 bg-green-100 text-green-700 rounded-md">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Daftar Tugas -->
                @if($tasks->isEmpty())
                    <p class="text-gray-500">Belum ada tugas. Tambahkan tugas pertamamu!</p>
                @else
                    <ul class="space-y-4">
                        @foreach($tasks as $task)
                        <li class="border-b pb-4 last:border-0">
                            <form method="POST" action="{{ route('tasks.update', $task) }}" class="flex items-start gap-3">
                                @csrf
                                @method('PUT')

                                <!-- Checkbox Status -->
                                <input type="checkbox" name="is_completed" 
                                    onchange="this.form.submit()"
                                    {{ $task->is_completed ? 'checked' : '' }}
                                    class="mt-1 h-5 w-5 text-indigo-600 rounded">

                                <!-- Judul & Deskripsi -->
                                <div class="flex-1">
                                    <input type="text" name="title" value="{{ $task->title }}"
                                        class="font-semibold w-full bg-transparent border-none p-0 focus:outline-none focus:ring-0">
                                    @if($task->description)
                                        <p class="text-gray-600 text-sm mt-1">{{ $task->description }}</p>
                                    @endif
                                    @if($task->deadline)
                                        <p class="text-xs text-gray-500 mt-1">Batas: {{ $task->deadline->format('d M Y') }}</p>
                                    @endif
                                </div>

                                <!-- Tombol Hapus -->
                                <form method="POST" action="{{ route('tasks.destroy', $task) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Yakin hapus tugas ini?')"
                                        class="text-red-500 hover:text-red-700 ml-2">
                                        Hapus
                                    </button>
                                </form>
                            </form>
                        </li>
                        @endforeach
                    </ul>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection