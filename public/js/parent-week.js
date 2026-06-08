/* ===== Lịch sử theo tuần (trang phụ huynh) =====
   Dữ liệu mẫu — sau này thay bằng dữ liệu thật từ Laravel (truyền qua JSON). */
const WD = ['T2','T3','T4','T5','T6','T7','CN'];
const STATUS = {
  present:{ic:'✓',cls:'present',lab:'Có mặt'},
  absent :{ic:'✕',cls:'off',lab:'Vắng không phép'},
  excused:{ic:'△',cls:'off',lab:'Vắng phép'},
  off    :{ic:'✕',cls:'off',lab:'Nghỉ'},
  makeup :{ic:'↻',cls:'makeup',lab:'Học bù'},
  study  :{ic:'•',cls:'study',lab:'Sắp học'}
};
// null = không có buổi. Mỗi tuần 7 ngày T2..CN.
const WEEKS = [
  {label:'Tuần 25 – 31/05/2026', days:['25','26','27','28','29','30','31'],
   st:['present',null,'present',null,'excused',null,null], time:'17:30'},
  {label:'Tuần 01 – 07/06/2026', days:['01','02','03','04','05','06','07'],
   st:['off',null,'present',null,'present',null,'makeup'], time:'17:30'},
  {label:'Tuần 08 – 14/06/2026', days:['08','09','10','11','12','13','14'],
   st:['study',null,'study',null,'study',null,null], time:'17:30'}
];
let wIdx = 1; // bắt đầu ở tuần hiện tại

function cellHtml(wd, date, st){
  if(!st) return '<div class="wcell none"><div class="wd">'+wd+'</div><div class="dn">'+date+'</div></div>';
  const s = STATUS[st];
  return '<div class="wcell '+s.cls+'"><div class="wd">'+wd+'</div><div class="dn">'+date+'</div><div class="ws">'+s.ic+'</div></div>';
}
function gridHtml(w){ return w.days.map((d,i)=>cellHtml(WD[i], d, w.st[i])).join(''); }

function renderThisWeek(){
  const g = document.getElementById('thisweek-grid');
  if(g) g.innerHTML = gridHtml(WEEKS[1]);
}

function renderHistory(){
  const w = WEEKS[wIdx];
  const lab = document.getElementById('weekLabel'); if(!lab) return;
  lab.textContent = w.label;
  document.getElementById('histGrid').innerHTML = gridHtml(w);
  document.getElementById('wprev').disabled = (wIdx <= 0);
  document.getElementById('wnext').disabled = (wIdx >= WEEKS.length - 1);

  let det='', np=0, no=0, nb=0;
  w.days.forEach(function(d,i){
    const st = w.st[i]; if(!st) return;
    const s = STATUS[st];
    const color = st==='present' ? 'var(--green-soft);color:var(--green)' :
                  st==='makeup'  ? 'var(--blue-soft);color:var(--blue)' :
                  st==='study'   ? 'var(--amber-soft);color:var(--amber)' :
                  st==='excused' ? 'var(--amber-soft);color:var(--amber)' :
                                   'var(--red-soft);color:var(--red)';
    det += '<div class="prow"><div>'+WD[i]+' '+d+'/06<div class="r">'+
           (st==='off' ? 'Nghỉ lễ/cô bận' : w.time+' · Toán 9')+'</div></div>'+
           '<span class="badge" style="background:'+color+'">'+s.lab+'</span></div>';
    if(st==='present'||st==='makeup') np++;
    else if(st==='off') no++;
    else if(st==='absent'||st==='excused') nb++;
  });
  if(!det) det = '<div class="prow" style="color:var(--muted)"><div>Không có buổi nào trong tuần này</div></div>';
  document.getElementById('histDetail').innerHTML = det;
  document.getElementById('histSummary').innerHTML =
    '<div class="prow"><div>Buổi đã học (gồm bù)</div><b>'+np+'</b></div>'+
    '<div class="prow"><div>Buổi nghỉ</div><b>'+no+'</b></div>'+
    '<div class="prow"><div>Vắng</div><b>'+nb+'</b></div>'+
    '<div class="prow"><div>Tiền phát sinh tuần này</div><b>'+(np*120).toLocaleString('vi-VN')+'.000đ</b></div>';
}
function weekStep(n){
  wIdx = Math.min(WEEKS.length - 1, Math.max(0, wIdx + n));
  renderHistory();
}
