import sherpa_onnx, soundfile as sf, json, sys, os
M=os.environ.get('TTS_MODEL','/tmp/claude-0/-home-user-sallatest/d8d3f76d-a835-504e-98cc-ff1925bfca37/scratchpad/tts/vits-piper-ar_JO-kareem-medium')
cfg=sherpa_onnx.OfflineTtsConfig(model=sherpa_onnx.OfflineTtsModelConfig(vits=sherpa_onnx.OfflineTtsVitsModelConfig(
    model=f'{M}/ar_JO-kareem-medium.onnx',tokens=f'{M}/tokens.txt',data_dir=f'{M}/espeak-ng-data'),num_threads=2))
tts=sherpa_onnx.OfflineTts(cfg)
lines=json.load(open(sys.argv[1],encoding='utf-8'))
out=sys.argv[2]; os.makedirs(out,exist_ok=True)
for k,(text,speed) in lines.items():
    a=tts.generate(text,sid=0,speed=speed)
    sf.write(f'{out}/{k}.wav',a.samples,a.sample_rate)
    print(k,round(len(a.samples)/a.sample_rate,2),'s',text)
