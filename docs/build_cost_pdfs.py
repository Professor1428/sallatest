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

# (الاسم, المواصفة التي تختار على أساسها, الكمية, أقل سعر للقطعة, أعلى سعر للقطعة, كلمات بحث أمازون, ملاحظة)
FULL={
 'ج١. العقل والتحكم (الكهرباء والإلكترونيات)':[
  ('لوحة ESP32 (DevKit)','ESP32 DevKit V1، ‏30 pin، ‏WiFi+Bluetooth، ‏CP2102 أو CH340',2,35,60,'ESP32 DevKit V1 30 pin WiFi Bluetooth','واحدة أساسية وواحدة احتياطية'),
  ('موديول ريليه ٤ قنوات','‏5V، ‏4 channel relay module، ‏optocoupler',1,20,40,'4 channel relay module 5V optocoupler','لتشغيل المروحة والقفل'),
  ('موديول MOSFET للإضاءة (PWM)','‏N-channel logic level، يدعم 12V، يعمل بجهد 3.3V',3,12,30,'MOSFET driver module PWM 12V logic level N channel','٣ قنوات للأحمر والأخضر والأزرق'),
  ('محول جهد 12V إلى 5V','‏LM2596 buck converter، ‏3A',2,12,30,'LM2596 DC-DC buck converter 3A','لتغذية ESP32 من مصدر 12V'),
  ('حساس حرارة ورطوبة','‏DHT22 (AM2302)',1,15,30,'DHT22 AM2302 temperature humidity sensor',''),
  ('بريدبورد وأسلاك توصيل','بريدبورد ‏830 نقطة + أسلاك ذكر/أنثى',1,20,40,'breadboard 830 jumper wires kit',''),
  ('أسلاك كهرباء وكتل توصيل','سلك ‏2 core ‏18AWG + terminal block',1,25,50,'18AWG 2 core wire terminal block connector kit',''),
 ],
 'ج٢. أجهزة الجناح (ما يراه الزائر يتحرك)':[
  ('شريط LED ‏RGB 12V','‏5050 RGB، ‏5 متر، ‏IP20',1,40,80,'RGB LED strip 12V 5050 5m','للألوان والأحمر'),
  ('شريط LED أبيض دافئ 12V','‏Warm white 3000K، ‏5 متر',2,30,60,'warm white LED strip 12V 3000K 5m','إضاءة الجناح الأساسية'),
  ('مصدر طاقة 12V','‏12V 10A، ‏120W، ‏LED power supply',1,60,110,'12V 10A 120W power supply LED','يكفي ١٥ متر شريط تقريبًا'),
  ('مروحة صغيرة','مروحة مكتب ‏USB أو ‏12V ‏120mm مع قاعدة',1,30,80,'USB desk fan small 5V','تُشغّل عبر الريليه'),
  ('قفل كهربائي (سولينويد)','‏12V solenoid lock، لباب خزانة أو باب مجسم',1,35,70,'12V electric solenoid lock cabinet','للباب الذكي'),
  ('سيرفو للحركة','‏SG90 أو ‏MG90S',2,10,25,'SG90 servo motor','اختياري لحركات صغيرة'),
 ],
 'ج٣. الصوت والكاميرا والعرض':[
  ('سبيكر فون USB (مايك + سماعة)','‏USB speakerphone، إلغاء ضوضاء، ‏omnidirectional',1,150,400,'USB conference speakerphone microphone noise cancelling','مهم جدًا لوضوح الصوت بالمعرض'),
  ('كاميرا ويب 1080p','‏1080p، ‏30fps، لتتبع اليد',1,150,350,'webcam 1080p 30fps USB','تعمل لتتبع الإيماءات'),
  ('سماعات خارجية','سماعات مكتب أو بلوتوث، ‏20W+ تقريبًا',1,80,250,'bluetooth speaker 20W portable','لصوت جارفيس العميق'),
  ('كابل HDMI + محول','‏HDMI 2.0، ‏3 متر',2,15,35,'HDMI cable 2.0 3m 4K',''),
  ('USB Hub بطاقة خارجية','‏USB 3.0، ‏7 منافذ، مع محول طاقة',1,40,90,'powered USB 3.0 hub 7 port',''),
 ],
 'ج٤. الجناح والاحتياط':[
  ('مشترك كهرباء','‏6 مخارج، حماية من الصعق',2,30,60,'power strip surge protector 6 outlet',''),
  ('كوابل USB وكابل ربط','‏USB-A إلى micro-USB / USB-C، ‏2 متر',4,10,25,'USB cable micro USB 2m',''),
  ('شريط لاصق وأربطة كوابل','‏Cable ties + gaffer tape',1,25,50,'cable ties gaffer tape assortment',''),
  ('جهاز واي فاي محمول (4G)','‏4G LTE router، ‏SIM unlocked (أو استخدم جوالك)',1,150,300,'4G LTE portable WiFi router SIM','نسخة احتياطية للإنترنت'),
 ],
}
FULL_OPT={
 'اختياري: شاشة وهولوغرام وطباعة':[
  ('شاشة 43 بوصة أو مونيتور 27','‏4K أو ‏Full HD، ‏HDMI (يُفضّل استعارتها أو تأجيرها)',1,450,1100,'27 inch monitor Full HD HDMI','إن لم تتوفر في الجناح'),
  ('ألواح أكريليك شفافة','‏3mm، ‏A3، لهرم الهولوغرام (Pepper\'s Ghost)',2,30,60,'clear acrylic sheet 3mm A3','يقطع ويركب هرم'),
  ('رول أب وطباعة QR','يُطبع في مطبعة محلية، مو من أمازون',1,80,150,'','مطبعة قريبة'),
 ]}
ECO={
 'ج١. الأساسي (برنامج على ويندوز فقط)':[
  ('مايك USB مكتبي أو سماعة رأس','‏USB condenser mic أو ‏headset بمايك، إلغاء ضوضاء',1,60,150,'USB microphone desktop condenser plug and play','أهم قطعة لجودة التعرف على الصوت'),
  ('سماعة بلوتوث','‏10W–20W تقريبًا',1,80,200,'bluetooth speaker 10W portable','أو استخدم سماعات اللابتوب'),
  ('كابل HDMI','‏HDMI 2.0، ‏3 متر، لعرض اللابتوب على شاشة الجناح',1,15,35,'HDMI cable 2.0 3m 4K',''),
  ('طباعة بوستر A1 + QR','يُطبع في مطبعة محلية، مو من أمازون',1,40,100,'','تعريف المشروع ورابط التجربة'),
 ],
}
ECO_OPT={
 'اختياري: كاميرا للإيماءات':[
  ('كاميرا ويب 1080p','تُستخدم لتتبع اليد (إن كانت كاميرا اللابتوب ضعيفة)',1,150,300,'webcam 1080p 30fps USB','يمكن تجاهلها واستخدام كاميرا اللابتوب'),
 ],
 'اختياري: مجسم مصغّر بسيط (يعطي لمسة حقيقية)':[
  ('لوحة ESP32','‏ESP32 DevKit V1',1,35,60,'ESP32 DevKit V1 30 pin WiFi Bluetooth',''),
  ('موديول ريليه ٤ قنوات','‏5V، ‏optocoupler',1,20,40,'4 channel relay module 5V optocoupler',''),
  ('شريط LED ‏RGB ‏12V ‏5 متر','‏5050 RGB',1,40,80,'RGB LED strip 12V 5050 5m',''),
  ('مصدر طاقة 12V ‏3A','‏12V 3A adapter',1,30,50,'12V 3A power adapter DC',''),
  ('بريدبورد وأسلاك','‏830 نقطة + أسلاك',1,20,40,'breadboard 830 jumper wires kit',''),
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
            lk=f'<a href="{link(qq)}">ابحث في أمازون السعودية</a><span class="u">{html.escape(link(qq))}</span>' if qq else 'غير متوفر على أمازون'
            nt=f'<br><span style="color:#5b6b7b">{bd(note)}</span>' if note else ''
            s+=f'<tr><td><b>{bd(n)}</b>{nt}</td><td>{bd(sp)}</td><td class="n">{q}</td><td class="n">{rng(a,b)}</td><td class="n">{rng(q*a,q*b)}</td><td>{lk}</td></tr>'
        ga=sum(r[2]*r[3] for r in rows);gb=sum(r[2]*r[4] for r in rows)
        s+=f'<tr class="tot"><td colspan="4">مجموع هذا القسم</td><td class="n" colspan="2">{rng(ga,gb)} ريال</td></tr></table>'
    return s
def doc(title,sub,tag,main,opt,intro,body_extra):
    a,b=tot(main);oa,ob=tot(opt)
    cover=f'<section class="page cover"><svg width="130" height="130" viewBox="0 0 170 170"><circle cx="85" cy="85" r="64" fill="none" stroke="#22d3ee" stroke-width="3"/><circle cx="85" cy="85" r="50" fill="none" stroke="#22d3ee" stroke-width="1.5" stroke-dasharray="6 6"/><circle cx="85" cy="85" r="28" fill="#22d3ee"/><circle cx="85" cy="85" r="13" fill="#07121f"/></svg><h1>{title}</h1><div class="s">{sub}</div><div class="tag">{tag}</div><div class="big">التكلفة التقديرية للأساسي<b>{rng(a,b)} ريال</b><span style="color:#9fc3d3;font-size:12px">+ اختياري: {rng(oa,ob)} ريال</span></div></section>'
    p1=f'<section class="page"><h2>ملخص التكلفة والملاحظات المهمة</h2>{intro}<div class="box warn"><b>تنبيه حول الأسعار والروابط:</b> الأسعار تقديرية بالريال السعودي وتتغير حسب العروض والبائع، ولم أتحقق منها مباشرة من أمازون. الروابط هي <b>روابط بحث</b> في amazon.sa بكلمات دقيقة، وليست صفحات منتجات محددة. اختر المنتج الذي يطابق «المواصفة» في الجدول ويقيّمه المشترون جيدًا، ثم راجع السعر قبل الشراء.</div>{rows_html(main)}<div class="pn">1</div></section>'
    p2=f'<section class="page">{rows_html(opt)}{body_extra}<div class="pn">2</div></section>'
    return f'<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><title>{title}</title><style>{CSS}</style></head><body>{cover}{p1}{p2}</body></html>'

full_intro=lambda: '<p>هذه النسخة تبني جناحًا تفاعليًا كاملًا: إضاءة حقيقية تنطفي وتتلوّن، ومروحة، وقفل ذكي، وكاميرا إيماءات، وصوت واضح. تحتاج وقتًا للتركيب (٣–٤ أيام) وخبرة بسيطة في التوصيل.</p>'
full_extra='''<h3>ملاحظات مهمة قبل الشراء</h3><ul>
<li><b>السلامة:</b> الجناح كله بجهد منخفض (12V و5V). لا توصّل جهد المنزل (220V) بالأجهزة المصنوعة يدويًا. مصدر 12V الجاهز هو الوحيد الموصول بالكهرباء.</li>
<li><b>الأسلاك:</b> شريط 10 متر يسحب حوالي 5–6 أمبير، فمصدر 10 أمبير كافٍ مع هامش أمان.</li>
<li><b>الباب:</b> القفل السولينويد يصلح لباب خزانة أو باب مجسم. لباب جناح فعلي اسأل منظمي المعرض عن المسموح.</li>
<li><b>الشاشة:</b> اسأل المنظمين قبل شراء شاشة، فكثير من الأجنحة تأتي بشاشة.</li>
<li><b>احتياط:</b> اشترِ لوحة ESP32 ثانية وكوابل زيادة. العطل أثناء العرض هو أكبر خطر.</li></ul>
<h3>خطة الشراء</h3><table><tr><th>الأولوية</th><th>ما تشتريه</th><th>لماذا</th></tr>
<tr><td class="n">١</td><td>الصوت (سبيكر فون + سماعة)</td><td>جودة الصوت أهم ما يقرّب الزائر</td></tr>
<tr><td class="n">٢</td><td>ESP32 + ريليه + MOSFET + شريط LED</td><td>الأنوار المتحركة هي لقطة الإبهار الأولى</td></tr>
<tr><td class="n">٣</td><td>كاميرا الويب</td><td>للإيماءات وللرؤية بـ Claude</td></tr>
<tr><td class="n">٤</td><td>القفل والمروحة والسيرفو</td><td>تُضاف بعد أن تعمل الأساسيات</td></tr></table>
<div class="box">لا تنسَ تكلفة <b>Claude API</b>: تُدفع حسب الاستخدام ولا تُشترى من أمازون. ضع حد إنفاق يوميًا في لوحة التحكم، وراجع الأسعار الحالية من موقع Anthropic.</div>'''

eco_intro=lambda: '''<p>النسخة الاقتصادية: جارفيس <b>برنامج يعمل على لابتوب ويندوز</b>، والجناح يُحاكى على الشاشة بصريًا (نفس مشهد الفيديو): الأنوار تخفت وتتلوّن على الشاشة، والمروحة تدور، والباب يفتح. لا حاجة لتوصيلات كهربائية، وتكلفتها أقل بكثير.</p>'''
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
