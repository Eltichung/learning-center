/* ===== Lịch sử theo tuần (trang phụ huynh) =====
   Dữ liệu thật do Laravel truyền qua window.LT_WEEKS (JSON).
   Nếu không có, dùng dữ liệu mẫu để vẫn xem được giao diện. */
const WD = ['T2','T3','T4','T5','T6','T7','CN'];
const STATUS = {
  present:{ic:'✓',cls:'present',lab:'Có mặt'},
  absent :{ic:'✕',cls:'off',lab:'Vắng không phép'},
  excused:{ic:'△',cls:'off',lab:'Vắng phép'},
  off    :{ic:'✕',cls:'off',lab:'Nghỉ'},
  makeup :{ic:'↻',cls:'makeup',lab:'Học bù'},
  study  :{ic:'•',cls:'study',lab:'Sắp học'}
};

const DEMO_WEEKS = [
  {label:'Tuần 25 – 31/05/2026', days:['25','26','27','28','29','30','31'],
   st:['present',null,'present',null,'excused',null,null], time:'17:30'},
  {label:'Tuần 01 – 07/06/2026', days:['01','02','03','04','05','06','07'],
   st:['off',null,'present',null,'present',null,'makeup'], time:'17:30'},
  {label:'Tuần 08 – 14/06/2026', days:['08','09','10','11','12','13','14'],
   st:['study',null,'study',null,'study',null,null], time:'17:30'}
];

const WEEKS = (window.LT_WEEKS && window.LT_WEEKS.length) ? window.LT_WEEKS : DEMO_WEEKS;
const PRICE_K = window.LT_PRICE_K || 120;          // đơn giá/buổi (nghìn đồng)
let wIdx = (typeof window.LT_WEEK_INDEX === 'number') ? window.LT_WEEK_INDEX : 1;

function cellHtml(wd, date, st){
  if(!st) return '<div class="wcell none"><div class="wd">'+wd+'</div><div class="dn">'+date+'</div></div>';
  const s = STATUS[st];
  return '<div class="wcell '+s.cls+'"><div class="wd">'+wd+'</div><div class="dn">'+date+'</div><div class="ws">'+s.ic+'</div></div>';
}
function gridHtml(w){ return w.days.map((d,i)=>cellHtml(WD[i], d, w.st[i])).join(''); }

function renderThisWeek(){
  const g = document.getElementById('thisweek-grid');
  if(g) g.innerHTML = gridHtml(WEEKS[wIdx] || WEEKS[0]);
}

function renderHistory(){
  const w = WEEKS[wIdx];
  const lab = document.getElementById('weekLabel'); if(!lab || !w) return;
  lab.textContent = w.label;
  document.getElementById('histGrid').innerHTML = gridHtml(w);
  document.getElementById('wprev').disabled = (wIdx <= 0);
  document.getElementById('wnext').disabled = (wIdx >= WEEKS.length - 1);

  const subj = w.subj || 'Buổi học';
  let det='', np=0, no=0, nbAbsent=0, nbExcused=0;
  w.days.forEach(function(d,i){
    const st = w.st[i]; if(!st) return;
    const s = STATUS[st];
    const mo = (w.mo && w.mo[i]) ? w.mo[i] : '';
    const color = st==='present' ? 'var(--green-soft);color:var(--green)' :
                  st==='makeup'  ? 'var(--blue-soft);color:var(--blue)' :
                  st==='study'   ? 'var(--amber-soft);color:var(--amber)' :
                  st==='excused' ? 'var(--amber-soft);color:var(--amber)' :
                                   'var(--red-soft);color:var(--red)';
    det += '<div class="prow"><div>'+WD[i]+' '+d+(mo?'/'+mo:'')+'<div class="r">'+
           (st==='off' ? 'Nghỉ lễ/cô bận' : w.time+' · '+subj)+'</div></div>'+
           '<span class="badge" style="background:'+color+'">'+s.lab+'</span></div>';
    if(st==='present'||st==='makeup') np++;
    else if(st==='off') no++;
    else if(st==='absent') nbAbsent++;   // vắng không phép — vẫn tính tiền
    else if(st==='excused') nbExcused++; // vắng có phép — miễn
  });
  if(!det) det = '<div class="prow" style="color:var(--muted)"><div>Không có buổi nào trong tuần này</div></div>';
  document.getElementById('histDetail').innerHTML = det;
  document.getElementById('histSummary').innerHTML =
    '<div class="prow"><div>Buổi đã học (gồm bù)</div><b>'+np+'</b></div>'+
    '<div class="prow"><div>Buổi nghỉ</div><b>'+no+'</b></div>'+
    '<div class="prow"><div>Vắng không phép (tính tiền)</div><b>'+nbAbsent+'</b></div>'+
    '<div class="prow"><div>Vắng có phép (miễn)</div><b>'+nbExcused+'</b></div>'+
    '<div class="prow"><div>Tiền phát sinh tuần này</div><b>'+((np+nbAbsent)*PRICE_K).toLocaleString('vi-VN')+'.000đ</b></div>';
}
function weekStep(n){
  wIdx = Math.min(WEEKS.length - 1, Math.max(0, wIdx + n));
  renderHistory();
}
