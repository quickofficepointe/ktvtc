@extends('ktvtc.admin.layout.adminlayout')
@section('content')

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Registered Users Card -->
    <div class="bg-white shadow-lg rounded-lg p-6 text-center cursor-pointer"
         onclick="openModal()">
        <h3 class="font-semibold text-lg text-dark">Registered Users</h3>
        <p class="text-3xl font-bold text-primary mt-3">{{ $totalUsers }}</p>
        <p class="text-sm text-gray-600">Click to manage users</p>
    </div>
</div>

<!-- Modal with DataTable -->
<div id="usersModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl p-6 relative">
        <button onclick="closeModal()" class="absolute top-3 right-3 text-gray-500 hover:text-dark">
            <i class="fas fa-times"></i>
        </button>

        <h2 class="text-xl font-semibold mb-4">Registered Users</h2>
        <div class="overflow-x-auto">
            <table id="usersTable" class="min-w-full border border-gray-200 rounded-lg">
                <thead>
                    <tr class="bg-gray-100 text-left text-sm">
                        <th class="p-2 border">Profile</th>
                        <th class="p-2 border">Name</th>
                        <th class="p-2 border">Email</th>
                        <th class="p-2 border">Phone</th>
                        <th class="p-2 border">Bio</th>
                        <th class="p-2 border">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr class="text-sm">
                        <td class="p-2 border">
                            <img src="{{ asset('storage/'.$user->profile_picture) }}"
                                 class="w-10 h-10 rounded-full object-cover">
                        </td>
                        <td class="p-2 border">{{ $user->name }}</td>
                        <td class="p-2 border">{{ $user->email }}</td>
                        <td class="p-2 border">{{ $user->phone_number }}</td>
                        <td class="p-2 border">{{ $user->bio }}</td>
                        <td class="p-2 border">
                            <form action="{{ route('admin.users.approve', $user->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <select name="role" class="border rounded p-1 text-sm">
                                    <option value="1">Main School</option>
                                    <option value="2">Admin</option>
                                    <option value="3">Scholarship</option>
                                    <option value="4">Library</option>
                                    <option value="5">Student</option>
                                    <option value="6">Cafeteria</option>
                                    <option value="7">Finance</option>
                                    <option value="8">Trainers</option>
                                    <option value="9">Website</option>
                                </select>
                                <button type="submit" class="bg-primary text-white px-2 py-1 rounded text-xs hover:bg-dark">
                                    Approve
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function openModal() {
        document.getElementById('usersModal').classList.remove('hidden');
        document.getElementById('usersModal').classList.add('flex');
    }
    function closeModal() {
        document.getElementById('usersModal').classList.add('hidden');
    }
</script>

<!-- Optional: DataTables JS for search/sort -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        $('#usersTable').DataTable();
    });
</script>

@endsection
