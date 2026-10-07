"""Builds the two cost/parts PDFs (full booth build + economical Windows-software build).
Prices are rough SAR estimates; Amazon links are amazon.sa SEARCH links (not specific listings)."""
import html, subprocess, sys
from urllib.parse import quote_plus
OUT=sys.argv[1] if len(sys.argv)>1 else '.'
CHROME='/opt/pw-browsers/chromium-1194/chrome-linux/chrome'
import re
def bd(t):  # keep Latin/number runs left-to-right inside Arabic text
    t=html.escape(t.replace('\u200f',''))
    return re.sub(r"([A-Za-z0-9][A-Za-z0-9 .+\-/()%×'&;#]*[A-Za-z0-9)%'])",r'<bdi dir="ltr">\1</bdi>',t)
def rng(a,b): return f'<bdi dir="ltr">{a:,}–{b:,}</bdi>'
def link(q): return 'https://www.amazon.sa/s?k='+quote_plus(q)

# (الاسم, المواصفة التي تختار على أساسها, الكمية, أقل سعر للقطعة, أعلى سعر للقطعة, كلمات بحث أمازون ('' = شراء محلي), ملاحظة)
LOCAL='من محل محلي'
FULL={
 'ج١. هيكل البوث (٣×٣ متر، ثلاثة جدران) — استغنِ عنه إن وفّره المنظم':[
  ('ألواح MDF للجدران','سماكة ‏12–18mm، مقاس ‏1.22×2.44م (٧ ألواح لثلاثة جدران بارتفاع ‏2.4م)',7,70,120,'','محل أخشاب/ساكو/ايس'),
  ('هيكل خشبي للتثبيت','عوارض خشب ‏4×2سم، حوالي ‏40 متر طولي',1,100,200,'','محل أخشاب'),
  ('براغي ومسامير وزوايا تثبيت','طقم ‏screws + ‏L brackets',1,60,120,'screws and L brackets assortment kit','أو من ايس'),
  ('دهان جدران أبيض/رمادي','دهان داخلي ‏4 لتر + رولة وفرش',2,90,180,'','يغطي الجدران بسرعة'),
  ('رولة دهان وأدوات','طقم ‏roller + ‏brush + ‏tray',1,30,60,'paint roller brush tray kit',''),
  ('هيكل ألمنيوم/PVC لتعليق الأنوار','أنابيب ‏PVC ‏1 بوصة + وصلات (إطار علوي ‏3×3م)',1,120,250,'','محل سباكة/ساكو'),
  ('موكيت أو أرضية للبوث','‏3×3م، موكيت رولات أو بلاط تركيب (interlocking)',1,150,350,'interlocking floor tiles foam mat','تغطي الأرضية وتخفي الأسلاك'),
  ('سجادة مجلس حمراء','‏2×3م، لمسة سعودية',1,120,300,'red arabic area rug 2x3','تحت منطقة الزائر'),
 ],
 'ج٢. الإضاءة الحقيقية (تنطفئ وتتلوّن)':[
  ('لمبات ذكية RGB (واي فاي)','‏E27، ‏RGB + أبيض دافئ، تدعم التحكم عبر الشبكة المحلية (مثل Yeelight/Tuya)',4,35,80,'smart LED bulb E27 RGB WiFi','جرّب لمبة واحدة أولًا للتأكد من التحكم من البرنامج'),
  ('قاعدة لمبة معلقة بسلك وقابس','‏E27 pendant cord set مع قابس مناسب لكهرباء السعودية (Type G)',4,20,45,'E27 pendant lamp holder cord with plug','تعلّق من الهيكل العلوي'),
  ('قابس ذكي (واي فاي)','‏Type G، ‏10A+، معتمد، للمروحة وللإضاءة',2,30,60,'smart plug WiFi type G 10A','لا تعدّل أسلاك 220V بنفسك'),
  ('شريط LED ‏RGB ‏12V','‏5050 RGB، ‏5 متر، ‏IP20',1,40,80,'RGB LED strip 12V 5050 5m','إضاءة خلف الشاشة تتغير ألوانها'),
  ('شريط LED أبيض دافئ ‏12V','‏3000K، ‏5 متر',1,30,60,'warm white LED strip 12V 3000K 5m',''),
  ('مصدر طاقة ‏12V','‏12V 5A، ‏60W',1,40,80,'12V 5A power supply LED',''),
 ],
 'ج٣. العقل والأجهزة الصغيرة (ESP32)':[
  ('لوحة ESP32 ‏(DevKit)','‏ESP32 DevKit V1، ‏30 pin، ‏WiFi+Bluetooth',2,35,60,'ESP32 DevKit V1 30 pin WiFi Bluetooth','واحدة أساسية وواحدة احتياطية'),
  ('موديول ريليه ٤ قنوات','‏5V، ‏4 channel relay، ‏optocoupler',1,20,40,'4 channel relay module 5V optocoupler','للقفل وأجهزة أخرى'),
  ('موديول MOSFET للإضاءة ‏(PWM)','‏N-channel logic level، ‏12V',3,12,30,'MOSFET driver module PWM 12V logic level N channel','٣ قنوات RGB'),
  ('محول جهد ‏12V إلى ‏5V','‏LM2596، ‏3A',2,12,30,'LM2596 DC-DC buck converter 3A',''),
  ('حساس حرارة ورطوبة','‏DHT22',1,15,30,'DHT22 AM2302 temperature humidity sensor',''),
  ('قفل كهربائي ‏(سولينويد)','‏12V solenoid lock',1,35,70,'12V electric solenoid lock cabinet','للباب الذكي'),
  ('بريدبورد وأسلاك','‏830 نقطة + أسلاك ذكر/أنثى',1,20,40,'breadboard 830 jumper wires kit',''),
  ('أسلاك وكتل توصيل','‏18AWG ‏2 core + ‏terminal block',1,25,50,'18AWG 2 core wire terminal block connector kit',''),
 ],
 'ج٤. الأثاث والديكور والباب':[
  ('جلسة أرضية/مجلس عربي','طقم متكأ ومساند، يكفي ‏3 أشخاص',1,250,600,'arabic majlis floor cushions set','عنصر الهوية السعودي في الجناح'),
  ('طاولة قهوة صغيرة','ارتفاع ‏40–50سم',1,80,200,'small coffee table wooden',''),
  ('دلة وفناجين ضيافة (ديكور)','طقم دلة وفناجين',1,60,150,'arabic coffee dallah set',''),
  ('طاولة للمعدات','طاولة قابلة للطي ‏120سم',1,80,200,'folding table 120cm',''),
  ('باب مجسم بإطار ومفصلات','لوح باب ‏60×180سم + مفصلات + إطار',1,120,300,'','نجّارة محلية/ايس'),
  ('كرسي','كرسي مريح لك أثناء العرض',1,60,150,'folding chair padded',''),
 ],
 'ج٥. الشاشة والصوت والكاميرا':[
  ('شاشة تلفزيون ‏43 بوصة','‏4K أو ‏Full HD، ‏HDMI، ‏60Hz',1,700,1400,'43 inch smart TV 4K','يمكن استئجارها بأقل'),
  ('حامل شاشة أو قاعدة','حامل حائط أو ستاند أرضي ‏43 بوصة',1,80,200,'TV wall mount 32-55 inch',''),
  ('سبيكر فون ‏USB (مايك + سماعة)','‏USB speakerphone، إلغاء ضوضاء',1,150,400,'USB conference speakerphone microphone noise cancelling','أهم قطعة لوضوح الصوت'),
  ('كاميرا ويب ‏1080p','‏1080p، ‏30fps',1,150,350,'webcam 1080p 30fps USB','لتتبع اليد'),
  ('سماعات خارجية','‏20W تقريبًا، بلوتوث أو سلك',1,80,250,'bluetooth speaker 20W portable','لصوت جارفيس العميق'),
  ('كابل ‏HDMI ‏3 متر','‏HDMI 2.0',2,15,35,'HDMI cable 2.0 3m 4K',''),
  ('USB Hub بطاقة خارجية','‏USB 3.0، ‏7 منافذ',1,40,90,'powered USB 3.0 hub 7 port',''),
 ],
 'ج٦. الكهرباء والتمديدات والسلامة':[
  ('مشترك كهرباء ‏6 مخارج','حماية من الصعق، ‏Type G',3,30,60,'power strip surge protector 6 outlet type G',''),
  ('كوابل USB','‏micro-USB / ‏USB-C، ‏2 متر',4,10,25,'USB cable micro USB 2m',''),
  ('أغطية كوابل أرضية','‏cable cover floor ‏3 متر',2,40,90,'floor cable cover cord protector',''),
  ('أربطة كوابل وشريط لاصق','‏cable ties + gaffer tape',1,25,50,'cable ties gaffer tape assortment',''),
  ('طفاية حريق صغيرة','‏1kg ‏ABC، مطلوبة في كثير من المعارض',1,40,90,'ABC fire extinguisher 1kg','اسأل المنظم'),
  ('جهاز واي فاي محمول ‏(4G)','‏4G LTE router، ‏SIM unlocked',1,150,300,'4G LTE portable WiFi router SIM','احتياط للإنترنت'),
 ],
 'ج٧. الهوية والطباعة والنقل':[
  ('رول أب','‏85×200سم، طباعة عالية',1,80,150,'','مطبعة محلية'),
  ('طباعة شعار وفينيل للجدار الخلفي','فينيل/فليكس للشعار واسم المشروع',1,100,300,'','مطبعة محلية'),
  ('لوحة تعريف وQR','فوم بورد ‏A3 مطبوع',2,20,50,'','مطبعة محلية'),
  ('نقل وتحميل','سيارة نقل (ذهاب وإياب)',1,200,500,'','حسب المسافة'),
  ('أدوات تركيب (إن لم تتوفر)','دريل لاسلكي ومنشار ومتر وسلم',1,100,300,'','يمكن استعارتها'),
 ],
}
FULL_OPT={
 'اختياري: إبهار إضافي':[
  ('ألواح أكريليك شفافة','‏3mm، ‏A3، لهرم الهولوغرام (Pepper\'s Ghost)',2,30,60,'clear acrylic sheet 3mm A3',''),
  ('كراسي لضيفين','كراسي قابلة للطي',2,60,150,'folding chair padded',''),
  ('بطاقات هدايا للزوار','بطاقات مطبوعة برسالة من جارفيس',1,50,150,'','مطبعة محلية'),
 ]}
ECO={
 'ج١. الشاشة والصوت (قلب النسخة الاقتصادية)':[
  ('شاشة تلفزيون ‏43 بوصة (أو مونيتور ‏27)','‏Full HD، ‏HDMI',1,600,1200,'43 inch smart TV 4K','أو استأجرها من المنظم'),
  ('حامل أو ستاند للشاشة','حامل حائط أو قاعدة أرضية',1,80,200,'TV wall mount 32-55 inch',''),
  ('مايك ‏USB مكتبي أو سماعة رأس','‏USB condenser mic، إلغاء ضوضاء',1,60,150,'USB microphone desktop condenser plug and play','أهم قطعة لجودة التعرف على الصوت'),
  ('سماعة بلوتوث','‏10W–20W',1,80,200,'bluetooth speaker 10W portable',''),
  ('كابل ‏HDMI ‏3 متر','‏HDMI 2.0',1,15,35,'HDMI cable 2.0 3m 4K',''),
 ],
 'ج٢. لمسة إضاءة حقيقية (تُتحكم من برنامج ويندوز)':[
  ('لمبات ذكية RGB (واي فاي)','‏E27، تدعم التحكم عبر الشبكة المحلية (Yeelight/Tuya)',2,35,80,'smart LED bulb E27 RGB WiFi','تتحكم بها من البرنامج مباشرة بدون أسلاك'),
  ('قاعدة لمبة معلقة بقابس','‏E27 pendant cord set، ‏Type G',2,20,45,'E27 pendant lamp holder cord with plug',''),
  ('قابس ذكي (واي فاي)','‏Type G، ‏10A+',1,30,60,'smart plug WiFi type G 10A','لمروحة أو شريط إضاءة'),
 ],
 'ج٣. الجناح والأثاث والهوية (بسيط)':[
  ('طاولة عرض وكرسي','طاولة ‏120سم قابلة للطي + كرسي',1,140,350,'folding table 120cm','إن لم يوفرها المنظم'),
  ('مشترك كهرباء ‏6 مخارج','حماية من الصعق، ‏Type G',2,30,60,'power strip surge protector 6 outlet type G',''),
  ('أغطية كوابل وأربطة','‏cable cover + cable ties',1,40,90,'floor cable cover cord protector',''),
  ('رول أب','‏85×200سم',1,80,150,'','مطبعة محلية'),
  ('بوستر ‏A1 وQR مطبوع','تعريف المشروع ورابط التجربة',1,40,100,'','مطبعة محلية'),
  ('نقل وتحميل','سيارة صغيرة (ذهاب وإياب)',1,100,300,'','حسب المسافة'),
 ],
}
ECO_OPT={
 'اختياري: كاميرا للإيماءات وإضاءة إضافية':[
  ('كاميرا ويب ‏1080p','تُستخدم لتتبع اليد (إن كانت كاميرا اللابتوب ضعيفة)',1,150,300,'webcam 1080p 30fps USB','يمكن استخدام كاميرا اللابتوب'),
  ('شريط LED ‏RGB ‏12V + ‏ESP32 + مصدر ‏12V','مجسم أنوار خلف الشاشة',1,130,250,'RGB LED strip 12V 5050 5m','تحتاج ESP32 ومصدر طاقة (انظر النسخة الكاملة)'),
 ],
}
def tot(groups):
    a=b=0
    for rows in groups.values():
        for r in rows: a+=r[2]*r[3]; b+=r[2]*r[4]
    return a,b
CSS="""
@page{size:A4;margin:0}
:root{--c:#0b4f6c;--cy:#22d3ee;--g:#f5b942}
*{box-sizing:border-box}
body{margin:0;font-family:"DejaVu Sans","Noto Sans Arabic",sans-serif;color:#16212c;font-size:11.5px;line-height:1.75}
.page{width:210mm;min-height:297mm;padding:14mm 13mm;page-break-after:always;position:relative}
.page:last-child{page-break-after:auto}
.cover{background:radial-gradient(circle at 50% 38%,#0e3a52,#07121f 65%);color:#fff;text-align:center;padding-top:38mm;min-height:297mm}
.cover h1{font-size:40px;color:var(--cy);margin:8mm 0 2mm}.cover .s{font-size:18px;color:#cfe8f3}
.cover .tag{display:inline-block;margin-top:12mm;border:1px solid var(--g);color:var(--g);padding:3px 16px;border-radius:20px;font-size:13px}
.cover .big{margin:14mm auto 0;width:120mm;background:rgba(255,255,255,.07);border:1px solid #1f6a86;border-radius:12px;padding:6mm}
.cover .big b{display:block;font-size:30px;color:var(--g)}
h2{font-size:20px;color:var(--c);margin:0 0 3mm;padding-bottom:2mm;border-bottom:3px solid var(--cy)}
h3{font-size:14px;color:var(--c);margin:5mm 0 1.5mm}
p{margin:0 0 2.5mm}ul{margin:0 0 3mm;padding-right:18px}li{margin-bottom:1mm}
table{width:100%;border-collapse:collapse;margin:1.5mm 0 3mm;font-size:10.5px}
th{background:var(--c);color:#fff;padding:1.6mm 2mm;text-align:right}
td{padding:1.5mm 2mm;border-bottom:1px solid #d6e2ea;vertical-align:top}
tr{page-break-inside:avoid}tr:nth-child(even) td{background:#f5fafc}
td.n{text-align:center;white-space:nowrap}
a{color:#0a66c2;text-decoration:none;font-weight:bold}
.u{direction:ltr;font-size:8.5px;color:#7a8794;display:block;word-break:break-all;font-weight:normal}
.box{background:#eef8fb;border-right:4px solid var(--cy);padding:2.5mm 4mm;border-radius:4px;margin:3mm 0}
.warn{background:#fff6e0;border-right-color:var(--g)}
.tot td{background:#e3f1f7!important;font-weight:bold}
.pn{position:absolute;bottom:6mm;left:0;right:0;text-align:center;font-size:9px;color:#9aa9b6}
"""
def rows_html(groups):
    s=''
    for g,rows in groups.items():
        s+=f'<h3>{html.escape(g)}</h3><table><tr><th style="width:23%">القطعة</th><th style="width:27%">المواصفة التي تختار على أساسها</th><th>الكمية</th><th>سعر القطعة (ريال)</th><th>الإجمالي (ريال)</th><th style="width:22%">رابط أمازون</th></tr>'
        for n,sp,q,a,b,qq,note in rows:
            lk=f'<a href="{link(qq)}">ابحث في أمازون السعودية</a><span class="u">{html.escape(link(qq))}</span>' if qq else 'شراء محلي (بدون رابط)'
            nt=f'<br><span style="color:#5b6b7b">{bd(note)}</span>' if note else ''
            s+=f'<tr><td><b>{bd(n)}</b>{nt}</td><td>{bd(sp)}</td><td class="n">{q}</td><td class="n">{rng(a,b)}</td><td class="n">{rng(q*a,q*b)}</td><td>{lk}</td></tr>'
        ga=sum(r[2]*r[3] for r in rows);gb=sum(r[2]*r[4] for r in rows)
        s+=f'<tr class="tot"><td colspan="4">مجموع هذا القسم</td><td class="n" colspan="2">{rng(ga,gb)} ريال</td></tr></table>'
    return s
def summary(groups):
    r='<h3>ملخص التكلفة حسب القسم</h3><table><tr><th>القسم</th><th>التكلفة (ريال)</th></tr>'
    for g,rows in groups.items(): r+=f'<tr><td>{html.escape(g)}</td><td class="n">{rng(sum(x[2]*x[3] for x in rows),sum(x[2]*x[4] for x in rows))}</td></tr>'
    a,b=tot(groups); ca,cb=round(a*.1),round(b*.1)
    r+=f'<tr><td>احتياطي ‏10٪ (أشياء تنساها: أسلاك، مفاجآت)</td><td class="n">{rng(ca,cb)}</td></tr><tr class="tot"><td>الإجمالي الكامل</td><td class="n">{rng(a+ca,b+cb)}</td></tr></table>'
    return r
def doc(title,sub,tag,main,opt,intro,body_extra):
    a,b=tot(main);ca,cb=round(a*.1),round(b*.1);a+=ca;b+=cb;oa,ob=tot(opt)
    cover=f'<section class="page cover"><svg width="130" height="130" viewBox="0 0 170 170"><circle cx="85" cy="85" r="64" fill="none" stroke="#22d3ee" stroke-width="3"/><circle cx="85" cy="85" r="50" fill="none" stroke="#22d3ee" stroke-width="1.5" stroke-dasharray="6 6"/><circle cx="85" cy="85" r="28" fill="#22d3ee"/><circle cx="85" cy="85" r="13" fill="#07121f"/></svg><h1>{title}</h1><div class="s">{sub}</div><div class="tag">{tag}</div><div class="big">التكلفة الكاملة التقديرية (شاملة البوث والإضاءة والأثاث والطباعة والنقل + احتياطي ١٠٪)<b>{rng(a,b)} ريال</b><span style="color:#9fc3d3;font-size:12px">+ اختياري: {rng(oa,ob)} ريال</span></div></section>'
    p1=f'<section class="page"><h2>ملخص التكلفة والملاحظات المهمة</h2>{intro}<div class="box warn"><b>تنبيه حول الأسعار والروابط:</b> الأسعار تقديرية بالريال السعودي وتتغير حسب العروض والبائع، ولم أتحقق منها مباشرة من أمازون. الروابط هي <b>روابط بحث</b> في amazon.sa بكلمات دقيقة، وليست صفحات منتجات محددة. اختر المنتج الذي يطابق «المواصفة» في الجدول ويقيّمه المشترون جيدًا، ثم راجع السعر قبل الشراء.</div>{summary(main)}<div class="pn">1</div></section>'
    p1b=f'<section class="page">{rows_html(main)}<div class="pn">2</div></section>'
    p2=f'<section class="page">{rows_html(opt)}{body_extra}<div class="pn">3</div></section>'
    return f'<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><title>{title}</title><style>{CSS}</style></head><body>{cover}{p1}{p1b}{p2}</body></html>'

full_intro=lambda: '<p>هذه <b>التكلفة الكاملة</b> لجناح تفاعلي جاهز للعرض: هيكل البوث والجدران والأرضية والأنوار الحقيقية والأثاث والديكور والشاشة والصوت والكاميرا والكهرباء والطباعة والنقل. إن وفّر المنظم جدران البوث وأرضيته (مواد ‏ج١) تنزل التكلفة كثيرًا، فاسألهم أولًا. تحتاج ٣–٤ أيام للتركيب.</p>'
full_extra='''<h3>ملاحظات مهمة قبل الشراء</h3><ul>
<li><b>السلامة:</b> أنوار السقف لمبات ذكية جاهزة وقابس ذكي معتمد، فلا تلمس أسلاك 220V بنفسك. الأجهزة المصنوعة يدويًا (ESP32) كلها بجهد منخفض (12V و5V).</li><li><b>جرّب قبل أن تشتري الكل:</b> اشترِ لمبة ذكية واحدة وتأكد أنك تتحكم بها من البرنامج على الشبكة المحلية، ثم اشترِ الباقي.</li>
<li><b>الأسلاك:</b> شريط 10 متر يسحب حوالي 5–6 أمبير، فمصدر 10 أمبير كافٍ مع هامش أمان.</li>
<li><b>الباب:</b> القفل السولينويد يصلح لباب خزانة أو باب مجسم. لباب جناح فعلي اسأل منظمي المعرض عن المسموح.</li>
<li><b>المنظم:</b> اسأل عن مقاس الجناح، وهل الجدران والأرضية والشاشة والكهرباء مشمولة، وهل يُسمح بتعليق الأنوار من هيكل علوي.</li>
<li><b>احتياط:</b> اشترِ لوحة ESP32 ثانية وكوابل زيادة. العطل أثناء العرض هو أكبر خطر.</li></ul>
<h3>خطة الشراء</h3><table><tr><th>الأولوية</th><th>ما تشتريه</th><th>لماذا</th></tr>
<tr><td class="n">١</td><td>الصوت (سبيكر فون + سماعة)</td><td>جودة الصوت أهم ما يقرّب الزائر</td></tr>
<tr><td class="n">٢</td><td>ESP32 + ريليه + MOSFET + شريط LED</td><td>الأنوار المتحركة هي لقطة الإبهار الأولى</td></tr>
<tr><td class="n">٣</td><td>كاميرا الويب</td><td>للإيماءات وللرؤية بـ Claude</td></tr>
<tr><td class="n">٤</td><td>القفل والمروحة والسيرفو</td><td>تُضاف بعد أن تعمل الأساسيات</td></tr></table>
<div class="box">لا تنسَ تكلفة <b>Claude API</b>: تُدفع حسب الاستخدام ولا تُشترى من أمازون. ضع حد إنفاق يوميًا في لوحة التحكم، وراجع الأسعار الحالية من موقع Anthropic.</div>'''

eco_intro=lambda: '''<p>النسخة الاقتصادية: جارفيس <b>برنامج يعمل على لابتوب ويندوز</b> (تفترض أن اللابتوب عندك)، والجناح يُحاكى على الشاشة (نفس مشهد الفيديو): الأنوار تخفت وتتلوّن، والمروحة تدور، والباب يفتح. وتبقى لمسة حقيقية بلمبتين ذكيتين يتحكم بهما البرنامج مباشرة. الأرقام هنا <b>تكلفة كاملة</b> تشمل الشاشة والصوت والأثاث والطباعة والنقل، على افتراض أن جدران البوث وأرضيته يوفرها المنظم.</p>'''
eco_extra='''<h3>كيف تعمل النسخة الاقتصادية</h3><table><tr><th>الجزء</th><th>الأداة (مجانية غالبًا)</th></tr>
<tr><td>الواجهة والجناح الافتراضي</td><td>تطبيق ويب (HTML/JavaScript) يُفتح بـ Chrome أو Edge بوضع ملء الشاشة (kiosk)</td></tr>
<tr><td>التعرف على الصوت</td><td>Web Speech API (يحتاج إنترنت)، أو Vosk للعمل بدون إنترنت</td></tr>
<tr><td>العقل</td><td>Claude API مع Tool Use (تُدفع حسب الاستخدام)</td></tr>
<tr><td>صوت جارفيس</td><td>صوت ويندوز العربي السعودي (Microsoft Naayf) إن كانت حزمة اللغة العربية مثبتة، أو نموذج Piper المجاني</td></tr>
<tr><td>الإيماءات</td><td>MediaPipe Hands داخل المتصفح بكاميرا اللابتوب</td></tr></table>
<h3>ما تفقده مقارنة بالنسخة الكاملة</h3><table><tr><th>الميزة</th><th>النسخة الكاملة</th><th>النسخة الاقتصادية</th></tr>
<tr><td>الأنوار</td><td>حقيقية في الجناح</td><td>محاكاة على الشاشة (أو شريط LED واحد اختياري)</td></tr>
<tr><td>القفل والمروحة</td><td>أجهزة فعلية</td><td>رسوم متحركة على الشاشة</td></tr>
<tr><td>الإبهار البصري</td><td>عالٍ جدًا</td><td>جيد، ويعتمد على جودة الواجهة</td></tr>
<tr><td>وقت التجهيز</td><td>٣–٤ أيام</td><td>١–٢ يوم</td></tr>
<tr><td>خطر العطل</td><td>أعلى (أجهزة وأسلاك)</td><td>أقل بكثير</td></tr></table>
<div class="box"><b>نصيحتي:</b> ابدأ بهذه النسخة. إذا اشتغلت بثبات، أضف «المجسم المصغّر الاختياري» (ESP32 + شريط LED) لتأخذ لقطة الأنوار الحقيقية بتكلفة صغيرة، وتبقى الواجهة كلها برنامجًا واحدًا.</div>'''
for name,main,opt,title,sub,tag,intro,extra in [
 ('jarvis-full-parts-cost.pdf',FULL,FULL_OPT,'جارفيس السعودي','قائمة القطع والتكلفة: النسخة الكاملة (جناح بأجهزة حقيقية)','النسخة الكاملة · روابط أمازون السعودية',full_intro(),full_extra),
 ('jarvis-economy-windows-cost.pdf',ECO,ECO_OPT,'جارفيس السعودي','قائمة القطع والتكلفة: النسخة الاقتصادية (برنامج على ويندوز)','النسخة الاقتصادية · روابط أمازون السعودية',eco_intro(),eco_extra)]:
    h=f'{OUT}/{name[:-4]}.html';open(h,'w',encoding='utf-8').write(doc(title,sub,tag,main,opt,intro,extra))
    subprocess.run([CHROME,'--headless','--no-sandbox','--disable-gpu','--no-pdf-header-footer',f'--print-to-pdf={OUT}/{name}','file://'+h],check=True,stderr=subprocess.DEVNULL)
    a,b=tot(main);oa,ob=tot(opt);print(name,a,b,'opt',oa,ob)
