  <div class="tablewrap">
  <table class="cardify">
    <thead><tr><th>Giáo viên</th><th>Email</th><th>SĐT</th><th>Số lớp</th><th>Số HS</th><th>Trạng thái</th><th></th></tr></thead>
    <tbody>
      @forelse ($teachers as $t)
        <tr>
          <td class="cardhead"><b>{{ $t->name }}</b></td>
          <td data-label="Email">{{ $t->email }}</td>
          <td data-label="SĐT">{{ $t->phone ?: '—' }}</td>
          <td data-label="Số lớp">{{ $t->classes_count }}</td>
          <td data-label="Số HS">{{ $t->students_count }}</td>
          <td data-label="Trạng thái">@if ($t->status === 'locked')<span class="chip r">Đã khoá</span>@else<span class="chip g">Hoạt động</span>@endif</td>
          <td class="cardact" style="text-align:right"><a class="btn ghost sm" href="{{ route('admin.teacher', $t->id) }}">Quản lý</a></td>
        </tr>
      @empty
        <tr><td colspan="7" class="r" style="padding:16px">Chưa có giáo viên nào.</td></tr>
      @endforelse
    </tbody>
  </table>
  </div>
  @include('partials.pagination', ['paginator' => $teachers])
