{{-- Composer nhận xét ĐA LOẠI (dùng chung: modal điểm danh + trang hồ sơ HS).
     Mỗi nhận xét = 1 ô; trong ô chọn loại + viết. Bấm "＋ Thêm nhận xét" để thêm ô.
     Lưu = đồng bộ theo (học sinh, ngày, loại). Các ô do JS dựng (đọc data-types/data-templates). --}}
@php
  $cmtTypesJson = $commentTypes->map(fn ($t) => [
      'id' => (int) $t->id, 'name' => $t->name, 'icon' => $t->icon, 'color' => $t->color, 'style' => $t->paletteStyle(),
  ])->values();
  $cmtTplsJson = [];
  foreach ($commentTypes as $t) {
      $cmtTplsJson[(int) $t->id] = $commentTemplates->where('comment_type_id', $t->id)->pluck('body')->values();
  }
@endphp
<div class="cmt-multi"
     data-sid="{{ $sid ?? '' }}"
     @if (! empty($autoload)) data-autoload="1" @endif
     data-url-fordate="{{ route('teacher.student.comments.forDate', ['id' => '__SID__'], false) }}"
     data-url-sync="{{ route('teacher.student.comments.sync', ['id' => '__SID__'], false) }}"
     data-url-types="{{ route('teacher.commentTypes.store', [], false) }}"
     data-url-tpls="{{ route('teacher.commentTemplates.store', [], false) }}"
     data-types="{{ json_encode($cmtTypesJson) }}"
     data-templates="{{ json_encode((object) $cmtTplsJson) }}">
  <div class="field" style="max-width:200px"><label>Ngày</label>
    <input type="date" name="comment_date" class="cmt-date" required value="{{ $date ?? now()->toDateString() }}">
  </div>

  <div class="field">
    <label>Nhận xét theo loại <span style="color:var(--muted);font-weight:400">· mỗi loại một ô</span></label>
    <div class="cmt-box-list"></div>
    <div class="cmt-empty r" hidden>Bấm “＋ Thêm nhận xét” để bắt đầu.</div>
    <div class="cmt-box-actions">
      <button type="button" class="btn ghost sm cmt-add-box">＋ Thêm nhận xét</button>
      <button type="button" class="btn ghost sm cmt-add-newtype">＋ Thêm Loại nhận xét mới</button>
    </div>
    <div class="cmt-newtype" hidden>
      <input type="text" class="cmt-newtype-name" placeholder="Tên loại mới (VD: Kiểm tra)" maxlength="60">
      <button type="button" class="btn primary sm cmt-newtype-save">Thêm</button>
      <button type="button" class="btn ghost sm cmt-newtype-cancel">Huỷ</button>
    </div>
  </div>
</div>
