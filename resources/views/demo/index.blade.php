<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Demos</title>
        <style>
            body { font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, Helvetica Neue, Arial, "Apple Color Emoji", "Segoe UI Emoji"; margin: 24px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #e5e7eb; padding: 8px 12px; }
            th { background: #f9fafb; text-align: left; }
            form { margin: 12px 0; }
            input[type=text] { width: 280px; padding: 6px 8px; border: 1px solid #d1d5db; }
            button { padding: 6px 10px; border: 1px solid #d1d5db; background: #f3f4f6; cursor: pointer; }
        </style>
    </head>
    <body>
        <h1>Demos</h1>

        <form method="POST" action="{{ url('/demos') }}">
            @csrf
            <input type="text" name="title" placeholder="Title" required>
            <input type="text" name="description" placeholder="Description">
            <label>
                <input type="checkbox" name="is_active" value="1" checked>
                Active
            </label>
            <button type="submit">Create</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->title }}</td>
                        <td>{{ $item->description }}</td>
                        <td>{{ $item->is_active ? 'Yes' : 'No' }}</td>
                        <td>
                            <form method="POST" action="{{ url('/demos/'.$item->id) }}" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top:12px;">
            {{ $items->links() }}
        </div>
    </body>
</html>


