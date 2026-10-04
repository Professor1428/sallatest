import sherpa_onnx, soundfile as sf, numpy as np, json, sys, os
S='/tmp/claude-0/-home-user-sallatest/d8d3f76d-a835-504e-98cc-ff1925bfca37/scratchpad/tts'
VOICES={'jarvis':('vits-piper-ar_JO-SA_miro-high','ar_JO-SA_miro-high'),   # Saudi (ar-SA) voice
        'visitor':('vits-piper-ar_JO-kareem-medium','ar_JO-kareem-medium')}
def load(v):
    d,n=VOICES[v]
    m=sherpa_onnx.OfflineTtsVitsModelConfig(model=f'{S}/{d}/{n}.onnx',tokens=f'{S}/{d}/tokens.txt',data_dir=f'{S}/{d}/espeak-ng-data',noise_scale=0.55,noise_scale_w=0.75)
    return sherpa_onnx.OfflineTts(sherpa_onnx.OfflineTtsConfig(model=sherpa_onnx.OfflineTtsModelConfig(vits=m,num_threads=2)))
engines={}
lines=json.load(open(sys.argv[1],encoding='utf-8')); out=sys.argv[2]; os.makedirs(out,exist_ok=True)
for k,(text,speed,voice) in lines.items():
    if voice not in engines: engines[voice]=load(voice)
    a=engines[voice].generate(text,sid=0,speed=speed)
    x=np.array(a.samples,dtype=np.float32)
    x=np.concatenate([np.zeros(int(.06*a.sample_rate),np.float32),x,np.zeros(int(.2*a.sample_rate),np.float32)])  # padding avoids clipped edges
    sf.write(f'{out}/{k}.wav',x,a.sample_rate); print(k,voice,round(len(x)/a.sample_rate,2))
