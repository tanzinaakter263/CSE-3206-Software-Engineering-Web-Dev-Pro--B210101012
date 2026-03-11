<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio,line-clamp,container-queries"></script>
<style type ="text/tailwindcss">
    @layer utilities {
      .container {
        @apply px-10 mx-auto;
}


    .btn{
        @apply bg-green-600 text-white rounded py-2 px-4;
    }
}

  </style>
     <title>Document</title>
</head>
<body>
    <div class ="container">
        <div class="flex justify-between my-5">
        <h2 class="text-red-500 text-xl">Home</h2>

        <a href="/create" class="btn">Add New Post</a>
        </div>
        @if(session('success'))
        <h2 class="text-green-600" py-5 mx-auto>{{ session('success') }}</h2>   
        @endif 
        <div class="">
            <!-- Table -->
<div class="min-w-full">
  <div class="overflow-x-auto [&::-webkit-scrollbar]:h-2 [&::-webkit-scrollbar-thumb]:rounded-none [&::-webkit-scrollbar-track]:bg-scrollbar-track [&::-webkit-scrollbar-thumb]:bg-scrollbar-thumb">
    <table class="min-w-full divide-y divide-table-line border border-green-300 my-5">
      <thead class="bg-green-600 text-white">
        <tr>
          <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-muted-foreground-1 uppercase">Id</th>
          <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-muted-foreground-1 uppercase">Name</th>
          <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-muted-foreground-1 uppercase">Description</th>
          <th scope="col" class="px-6 py-3 text-end text-xs font-medium text-muted-foreground-1 uppercase">Image</th>
          <th scope="col" class="px-6 py-3 text-end text-xs font-medium text-muted-foreground-1 uppercase">Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($posts as $post)
          <tr class="odd:bg-layer even:bg-surface">
          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-foreground">{{ $post->id }}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $post->name }}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground">{{ $post->description }}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-foreground"><img src="images/{{ $post->image}}" width="80px" alt=""></td> 
          <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
          <a href="{{ route('edit',$post->id)}}" class="btn">Edit</a>
          <form method="post" action="{{ route('delete',$post->id) }}" class="inline">
            @csrf
            @method('delete')
            <button type="submit" class="bg-red-600 text-white rounded py-2 px-4">Delete</button>
          </form>
        </td>
        </tr>
        @endforeach
        
       </tbody>
    </table>

    {{ $posts->links() }}
  </div>
</div>
<!-- End Table -->


        </div>   
    </div>
    
</body>
</html>