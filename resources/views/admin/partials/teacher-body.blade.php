<div class="twocol">
  <div>
    <div class="panel"><div class="ph"><h3>Trạng thái tài khoản</h3></div><div class="pb" style="padding:16px">
      <p class="r" style="margin:0 0 12px;font-size:13px">
        {{ $teacher->status === 'locked'
            ? 'Tài khoản đang bị khoá — giáo viên không đăng nhập được.'
            : 'Tài khoản đang hoạt động bình thường.' }}
      </p>
      <form method="POST" action="{{ route('admin.teacher.toggleStatus', $teacher->id) }}"
            data-confirm="{{ $teacher->status === 'locked' ? 'Mở khoá tài khoản này?' : 'Khoá tài khoản này? Giáo viên sẽ không đăng nhập được.' }}"
            data-refetch="#admin-teacher-body">
        @csrf @method('PUT')
        @if ($teacher->status === 'locked')
          <button class="btn primary" type="submit">Mở khoá tài khoản</button>
        @else
          <button class="btn danger" type="submit">Khoá tài khoản</button>
        @endif
      </form>
    </div></div>

    <div class="panel"><div class="ph"><h3>Quyền (role)</h3></div><div class="pb" style="padding:16px">
      <form method="POST" action="{{ route('admin.teacher.role', $teacher->id) }}" data-confirm="Đổi quyền tài khoản này?" data-refetch="#admin-teacher-body">
        @csrf @method('PUT')
        <div class="field"><label>Role</label>
          <select name="role">
            <option value="owner" @selected($teacher->role === 'owner')>Giáo viên (owner)</option>
            <option value="super_admin" @selected($teacher->role === 'super_admin')>Quản trị (super_admin)</option>
          </select>
        </div>
        <button class="btn primary" type="submit">Lưu quyền</button>
      </form>
    </div></div>
  </div>

  <div>
    <div class="panel"><div class="ph"><h3>Gói & subscription</h3></div><div class="pb" style="padding:16px">
      @php($sub = $teacher->subscription)
      @php($curPlan = $sub?->plan)
      <p style="margin:0 0 10px;font-size:13px">
        Gói hiện tại:
        <b>{{ $curPlan?->name ?: '—' }}</b>
        @if ($sub && $sub->current_period_end && ($curPlan?->slug ?? '') !== 'trial')
          · Hạn dùng đến <b>{{ \Illuminate\Support\Carbon::parse($sub->current_period_end)->format('d/m/Y') }}</b>
          @if (\Illuminate\Support\Carbon::parse($sub->current_period_end)->endOfDay()->isPast())
            <span class="chip r" style="font-size:11px;margin-left:4px">Đã hết hạn</span>
          @endif
        @endif
      </p>
      <form method="POST" action="{{ route('admin.teacher.plan', $teacher->id) }}" data-confirm="Đặt gói cho giáo viên này?" data-refetch="#admin-teacher-body">
        @csrf @method('PUT')
        <div class="grid2">
          <div class="field"><label>Gói</label>
            <select name="plan" id="plan-set">
              @foreach ($plans as $p)
                <option value="{{ $p->slug }}" @selected($curPlan?->slug === $p->slug)>{{ $p->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="field" id="months-set-field">
            <label>Cộng thêm</label>
            <select name="months">
              <option value="1" selected>1 tháng</option>
              <option value="3">3 tháng</option>
              <option value="6">6 tháng</option>
              <option value="12">12 tháng</option>
            </select>
          </div>
        </div>
        <button class="btn primary" type="submit">Lưu gói</button>
        <div class="r" style="font-size:11px;margin-top:6px">Admin cấp thủ công — không thu tiền.</div>
      </form>
      <script>
        (function(){
          const s = document.getElementById('plan-set');
          const f = document.getElementById('months-set-field');
          function sync(){ f.style.display = (s.value === 'trial' || s.value === 'vip') ? 'none' : 'block'; }
          s.addEventListener('change', sync); sync();
        })();
      </script>
    </div></div>

    @if ($recentOrders->isNotEmpty())
    <div class="panel"><div class="ph"><h3>Đơn thanh toán gần đây</h3></div><div class="pb">
      <div class="scrolllist">
        <table>
          <thead><tr><th>Ngày</th><th>Mã</th><th>Gói</th><th>Số tiền</th><th>Trạng thái</th></tr></thead>
          <tbody>
            @foreach ($recentOrders as $o)
              <tr>
                <td>{{ $o->created_at->format('d/m H:i') }}</td>
                <td><code style="font-size:11px">{{ $o->code }}</code></td>
                <td>{{ $o->plan->name }}</td>
                <td class="money">{{ number_format($o->amount,0,',','.') }}đ</td>
                <td>
                  @switch($o->status)
                    @case('pending')<span class="chip a">Chờ</span>@break
                    @case('approved')<span class="chip g">Đã kích hoạt</span>@break
                    @case('rejected')<span class="chip r">Từ chối</span>@break
                    @case('cancelled')<span class="chip n">Huỷ</span>@break
                  @endswitch
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div></div>
    @endif

    <div class="panel"><div class="ph"><h3>Đặt lại mật khẩu</h3></div><div class="pb" style="padding:16px">
      <form method="POST" action="{{ route('admin.teacher.password', $teacher->id) }}" data-confirm="Đặt lại mật khẩu cho giáo viên này?" data-refetch="#admin-teacher-body" data-reset-on-success>
        @csrf @method('PUT')
        <div class="field"><label>Mật khẩu mới <span style="color:var(--red)">*</span></label>
          <input type="password" name="password" required minlength="6" placeholder="Tối thiểu 6 ký tự"></div>
        <div class="field"><label>Nhập lại mật khẩu <span style="color:var(--red)">*</span></label>
          <input type="password" name="password_confirmation" required minlength="6"></div>
        <button class="btn primary" type="submit">Đặt lại mật khẩu</button>
      </form>
    </div></div>
  </div>
</div>
