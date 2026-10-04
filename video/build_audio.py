"""Generate voices (offline Arabic TTS), compute the video timeline from the
real audio durations, mix voices + sound effects, and write timeline.js."""
import json, os, subprocess, soundfile as sf
HERE=os.path.dirname(os.path.abspath(__file__))
S='/tmp/claude-0/-home-user-sallatest/d8d3f76d-a835-504e-98cc-ff1925bfca37/scratchpad'
RAW=f'{S}/raw'; FX=f'{S}/fx'; os.makedirs(FX,exist_ok=True)
subprocess.run(['python3',f'{HERE}/tts.py',f'{HERE}/lines.json',RAW],check=True,stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
def ff(*a): subprocess.run(['ffmpeg','-y','-loglevel','error',*a],check=True)
keys=list(json.load(open(f'{HERE}/lines.json',encoding='utf-8')).keys())
for k in keys:   # no pitch-shifting (it caused the robotic/choppy sound): just clean + level each voice
    if k.startswith('v'):
        f='highpass=f=80,lowpass=f=9000,acompressor=threshold=-20dB:ratio=3:attack=5:release=80,loudnorm=I=-17:TP=-2:LRA=7,afade=t=in:d=0.02'
    else:
        f='highpass=f=70,lowpass=f=10000,equalizer=f=2800:t=q:w=1.0:g=2,acompressor=threshold=-20dB:ratio=3:attack=5:release=80,aecho=0.85:0.3:22:0.10,loudnorm=I=-16:TP=-2:LRA=7,afade=t=in:d=0.02'
    ff('-i',f'{RAW}/{k}.wav','-af',f,'-ar','44100',f'{FX}/{k}.wav')
D={k:sf.info(f'{FX}/{k}.wav').duration for k in keys}

sp=[];th=[];rep=[];logs=[];lights=[];place={}
def say(k,t): place[k]=t; return t+D[k]
t=16.5
# 1: introduce
e=say('v1',t); sp.append(dict(s=t,e=e,t="يا جارفيس، عرّف نفسك للضيوف"))
th.append([e+.1,e+.8]); t=e+.8
e=say('j1',t); rep.append(dict(s=t,e=e,l=["هلا والله! أنا جارفيس، مساعدك الذكي.","أفهم لهجتك وأتحكم بكل شي في البيت."]))
cap1_end=e
# 2: lights off
t=e+1.0; e=say('v2',t); sp.append(dict(s=t,e=e,t="طفّ الأنوار")); th.append([e+.1,e+.7]); t=e+.7
cap2_start=cap1_end+.6
lights.append(dict(t=t+.1,on=0,red=0)); logs.append(dict(t=t,x='control_light("off")'))
e=say('j2',t); rep.append(dict(s=t,e=e,l=["أبشر، طفّيت الأنوار."]))
# 3: red
t=e+.9; e=say('v3',t); sp.append(dict(s=t,e=e,t="شغّلها، وخلّها حمرا")); th.append([e+.1,e+.7]); t=e+.7
lights.append(dict(t=t+.1,on=1,red=1)); logs.append(dict(t=t,x='control_light("on", color="red")'))
e=say('j3',t); rep.append(dict(s=t,e=e,l=["تم، شغّلتها باللون الأحمر."]))
cap2_end=e
# 4: sleep
t=e+1.2; sleep0=t; e=say('v4',t); sp.append(dict(s=t,e=e,t="أنا بنام")); th.append([e+.1,e+.9]); t=e+.9
rs=t
logs.append(dict(t=t,x='get_prayer_times()'))
e=say('j4a',t); t=e+.12
logs.append(dict(t=t,x='control_light("off")')); lights.append(dict(t=t+.1,on=0,red=0))
e=say('j4b',t); t=e+.12
logs.append(dict(t=t,x='control_fan("on")')); fan=t+.1
e=say('j4c',t); t=e+.12
logs.append(dict(t=t,x='set_alarm("04:45")')); alarm=t
e=say('j4d',t)
rep.append(dict(s=rs,e=e,l=["تصبح على خير! أطفأت الأنوار وشغلت المروحة،","وضبطت المنبه على ٤:٤٥ لصلاة الفجر."]))
cap3_end=e
# 5: door
t=e+1.1; door_cap=t; e=say('v5',t); sp.append(dict(s=t,e=e,t="افتح الباب")); th.append([e+.1,e+.7]); t=e+.7
logs.append(dict(t=t,x='unlock_door()')); door=[t+.1,0]
e=say('j5',t); rep.append(dict(s=t,e=e,l=["تفضّل، الباب مفتوح."]))
# 6: lights on
t=e+1.0; e=say('v6',t); sp.append(dict(s=t,e=e,t="شغّل الأنوار")); th.append([e+.1,e+.7]); t=e+.7
logs.append(dict(t=t,x='control_light("on")')); lights.append(dict(t=t+.1,on=1,red=0))
e=say('j6',t); rep.append(dict(s=t,e=e,l=["حاضر، صباح الخير."]))
door[1]=e+.3
cap6_end=e
# 7: gesture
g0=e+1.2; say('j7',g0+.8); g1=g0+8.5
rep.append(dict(s=g0+.8,e=g0+.8+D['j7'],l=["أشوف يدك.","تحكّم بالواجهة بحركة وحدة."]))
d0=g1+.6; d1=d0+9.3; end=d1+6.2
walk=[11,16.3]
caps=[dict(s=5,e=11,t="هذا جناحك في المعرض: غرفة بحجم بيت، فيها شاشة جارفيس، وإضاءة، ومروحة، وباب ذكي"),
 dict(s=11,e=16.3,t="يدخل الزائر الجناح ليجرّب بنفسه"),
 dict(s=16.5,e=cap1_end,t="يكلّم جارفيس بلهجته، وجارفيس يرد عليه بصوت عربي"),
 dict(s=cap1_end+.5,e=cap2_end,t="أنوار الجناح تنطفي وتتلوّن بصوته، كأنه بيت حقيقي"),
 dict(s=sleep0,e=door_cap-.2,t="بأمر واحد «أنا بنام» يقرر جارفيس الخطوات بنفسه: أنوار، مروحة، منبه"),
 dict(s=door_cap,e=door[1]-1,t="وحتى باب الجناح يُفتح بصوته عبر قفل ذكي"),
 dict(s=door[1]-1,e=cap6_end+.4,t="ويرجّع الأنوار متى ما طلب"),
 dict(s=g0,e=g1,t="وبحركة اليد: يلوّح الزائر فتتفاعل الواجهة معه"),
 dict(s=d0,e=d1,t="الصوت يتحول لنص، Claude يقرر الأدوات، وESP32 ينفّذ على الأجهزة")]
TL=dict(walk=walk,speeches=sp,thinks=th,replies=rep,logs=logs,lights=lights,fan=fan,door=door,gest=[g0,g1],diag=[d0,d1],end=end,caps=caps,alarm=alarm)
open(f'{HERE}/timeline.js','w',encoding='utf-8').write('const TL='+json.dumps(TL,ensure_ascii=False)+';')
json.dump(TL,open(f'{S}/tl.json','w'))

# ---- mix ----
ins=[];fl=[];n=0
def add(path,at,vol=1.0,extra=''):
    global n
    ins.extend(['-i',path]); ms=int(at*1000)
    fl.append(f'[{n}:a]{extra}adelay={ms}|{ms},volume={vol}[a{n}]'); n+=1
ff('-f','lavfi','-i','sine=f=1300:d=0.09','-af','afade=t=out:st=0.04:d=0.05,volume=0.5',f'{FX}/blip.wav')
ff('-f','lavfi','-i','sine=f=880:d=0.5','-af','afade=t=out:st=0.05:d=0.45,volume=0.4,aecho=0.8:0.6:120:0.4',f'{FX}/chime.wav')
ff('-f','lavfi','-i','anoisesrc=d=1.4:c=brown:a=0.5','-af','bandpass=f=300:w=2,afade=t=in:d=0.1,afade=t=out:st=1.0:d=0.4,volume=1.2',f'{FX}/servo.wav')
ff('-f','lavfi','-i',f'anoisesrc=d={d0-fan:.2f}:c=brown:a=0.5','-af',f'lowpass=f=500,afade=t=in:d=1.5,afade=t=out:st={d0-fan-1.5:.2f}:d=1.5,volume=0.5',f'{FX}/fan.wav')
for k,at in place.items(): add(f'{FX}/{k}.wav',at,1.0)
for l in logs: add(f'{FX}/blip.wav',l['t'],0.8)
add(f'{FX}/chime.wav',alarm+.2,0.8); add(f'{FX}/servo.wav',door[0],0.9); add(f'{FX}/servo.wav',door[1],0.7); add(f'{FX}/fan.wav',fan,0.6)
ff(*ins,'-filter_complex',';'.join(fl)+';'+''.join(f'[a{i}]' for i in range(n))+f'amix=inputs={n}:normalize=0,alimiter=limit=0.95,apad=whole_dur={end}[o]','-map','[o]','-t',str(end),'-ar','44100',f'{S}/mix.wav')
print('end',round(end,1),'voices',{k:round(v,1) for k,v in D.items()})
