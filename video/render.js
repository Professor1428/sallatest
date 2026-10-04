const {chromium}=require('/opt/node-tools/node_modules/playwright');
const fs=require('fs');
(async()=>{
 const [,, outDir, fps]=process.argv;
 const F=+fps;
 fs.mkdirSync(outDir,{recursive:true});
 const b=await chromium.launch();
 const p=await b.newPage({viewport:{width:1280,height:720}});
 p.on('pageerror',e=>{console.log('ERR',e.message);process.exit(1)});
 await p.goto('file://'+__dirname+'/scene.html');
 const D=await p.evaluate(()=>TL.end);
 const n=Math.floor(D*F);
 for(let i=0;i<n;i++){
  await p.evaluate(t=>render(t),i/F);
  await p.screenshot({path:`${outDir}/f${String(i).padStart(5,'0')}.jpg`,type:'jpeg',quality:88});
 }
 await b.close();
})();
