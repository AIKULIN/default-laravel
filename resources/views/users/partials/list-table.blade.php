<section>
    <header>
        <h2 class="text-lg font-medium ">
            {{ __('Users list') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('') }}
        </p>
    </header>

    <table class="w-full whitespace-no-wrapw-full whitespace-no-wrap">
        <thead>
        <tr>
            <td>No</td>
            <td>Name</td>
            <td>Email</td>
            <td>Roles</td>
            <td width="280px">Action</td>
        </tr>
        </thead>
        @foreach ($data as $key => $user)
            <tr>
                <td>{{ ++$i }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    @if(!empty($user->getRoleNames()))
                        @foreach($user->getRoleNames() as $v)
                            <label class="badge bg-success">{{ $v }}</label>
                        @endforeach
                    @endif
                </td>
                <td>
                    <a class="outline outline-offset-2" href="{{ route('manage.show',$user->id) }}">Show</a>
                    <a class="btn btn-primary" href="{{ route('manage.edit',$user->id) }}">Edit</a>
                    <x-primary-button :>{{ __('Save') }}</x-primary-button>
                </td>
            </tr>
        @endforeach
    </table>

    <div class="mt-6">
        {{ $data->links() }}
    </div>
</section>
