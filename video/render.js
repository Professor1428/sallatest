const {chromium}=require('/opt/node-tools/node_modules/playwright');
const fs=require('fs');
(async()=>{
 const [,, outDir, fps, dur, start]=process.argv;
 const F=+fps, D=+dur, S=+(start||0);
 fs.mkdirSync(outDir,{recursive:true});
 const b=await chromium.launch();
 const p=await b.newPage({viewport:{width:1280,height:720}});
 await p.goto('file:///home/user/sallatest/video/scene.html');
 const n=Math.floor(D*F);
 for(let i=0;i<n;i++){
  await p.evaluate(t=>render(t),S+i/F);
  await p.screenshot({path:`${outDir}/f${String(i).padStart(5,'0')}.jpg`,type:'jpeg',quality:88});
 }
 await b.close();
})();
