"use strict";(()=>{var we=n=>n;var An=we,Ye=we;var Cn=(n,t,e)=>{let i=t-n;return i===0?1:(e-n)/i};function Mi(n){let t;return()=>(t===void 0&&(t=n()),t)}var zc=Mi(()=>window.ScrollTimeline!==void 0);var $s=class{constructor(t){this.stop=()=>this.runAll("stop"),this.animations=t.filter(Boolean)}get finished(){return Promise.all(this.animations.map(t=>"finished"in t?t.finished:t))}getAll(t){return this.animations[0][t]}setAll(t,e){for(let i=0;i<this.animations.length;i++)this.animations[i][t]=e}attachTimeline(t,e){let i=this.animations.map(s=>{if(zc()&&s.attachTimeline)return s.attachTimeline(t);if(typeof e=="function")return e(s)});return()=>{i.forEach((s,r)=>{s&&s(),this.animations[r].stop()})}}get time(){return this.getAll("time")}set time(t){this.setAll("time",t)}get speed(){return this.getAll("speed")}set speed(t){this.setAll("speed",t)}get startTime(){return this.getAll("startTime")}get duration(){let t=0;for(let e=0;e<this.animations.length;e++)t=Math.max(t,this.animations[e].duration);return t}runAll(t){this.animations.forEach(e=>e[t]())}flatten(){this.runAll("flatten")}play(){this.runAll("play")}pause(){this.runAll("pause")}cancel(){this.runAll("cancel")}complete(){this.runAll("complete")}};var Si=class extends $s{then(t,e){return Promise.all(this.animations).then(t).catch(e)}};var ge=n=>n*1e3,Te=n=>n/1e3;function bi(n){let t=0,e=50,i=n.next(t);for(;!i.done&&t<2e4;)t+=e,i=n.next(t);return t>=2e4?1/0:t}var Zs=(n,t,e=10)=>{let i="",s=Math.max(Math.round(t/e),2);for(let r=0;r<s;r++)i+=n(Cn(0,s-1,r))+", ";return`linear(${i.substring(0,i.length-2)})`};var De=(n,t,e)=>e>t?t:e<n?n:e;function Js(n,t){return t?n*(1e3/t):0}var dd=5;function Qs(n,t,e){let i=Math.max(t-dd,0);return Js(e-n(i),t-i)}var jt={stiffness:100,damping:10,mass:1,velocity:0,duration:800,bounce:.3,visualDuration:.3,restSpeed:{granular:.01,default:2},restDelta:{granular:.005,default:.5},minDuration:.01,maxDuration:10,minDamping:.05,maxDamping:1};var ea=.001;function kc({duration:n=jt.duration,bounce:t=jt.bounce,velocity:e=jt.velocity,mass:i=jt.mass}){let s,r;An(n<=ge(jt.maxDuration),"Spring duration must be 10 seconds or less");let o=1-t;o=De(jt.minDamping,jt.maxDamping,o),n=De(jt.minDuration,jt.maxDuration,Te(n)),o<1?(s=c=>{let h=c*o,d=h*n,p=h-e,m=js(c,o),g=Math.exp(-d);return ea-p/m*g},r=c=>{let d=c*o*n,p=d*e+e,m=Math.pow(o,2)*Math.pow(c,2)*n,g=Math.exp(-d),_=js(Math.pow(c,2),o);return(-s(c)+ea>0?-1:1)*((p-m)*g)/_}):(s=c=>{let h=Math.exp(-c*n),d=(c-e)*n+1;return-ea+h*d},r=c=>{let h=Math.exp(-c*n),d=(e-c)*(n*n);return h*d});let a=5/n,l=md(s,r,a);if(n=ge(n),isNaN(l))return{stiffness:jt.stiffness,damping:jt.damping,duration:n};{let c=Math.pow(l,2)*i;return{stiffness:c,damping:o*2*Math.sqrt(i*c),duration:n}}}var pd=12;function md(n,t,e){let i=e;for(let s=1;s<pd;s++)i=i-n(i)/t(i);return i}function js(n,t){return n*Math.sqrt(1-t*t)}var gd=["duration","bounce"],xd=["stiffness","damping","mass"];function Hc(n,t){return t.some(e=>n[e]!==void 0)}function _d(n){let t={velocity:jt.velocity,stiffness:jt.stiffness,damping:jt.damping,mass:jt.mass,isResolvedFromDuration:!1,...n};if(!Hc(n,xd)&&Hc(n,gd))if(n.visualDuration){let e=n.visualDuration,i=2*Math.PI/(e*1.2),s=i*i,r=2*De(.05,1,1-(n.bounce||0))*Math.sqrt(s);t={...t,mass:jt.mass,stiffness:s,damping:r}}else{let e=kc(n);t={...t,...e,mass:jt.mass},t.isResolvedFromDuration=!0}return t}function Ei(n=jt.visualDuration,t=jt.bounce){let e=typeof n!="object"?{visualDuration:n,keyframes:[0,1],bounce:t}:n,{restSpeed:i,restDelta:s}=e,r=e.keyframes[0],o=e.keyframes[e.keyframes.length-1],a={done:!1,value:r},{stiffness:l,damping:c,mass:h,duration:d,velocity:p,isResolvedFromDuration:m}=_d({...e,velocity:-Te(e.velocity||0)}),g=p||0,_=c/(2*Math.sqrt(l*h)),u=o-r,f=Te(Math.sqrt(l/h)),b=Math.abs(u)<5;i||(i=b?jt.restSpeed.granular:jt.restSpeed.default),s||(s=b?jt.restDelta.granular:jt.restDelta.default);let S;if(_<1){let C=js(f,_);S=A=>{let T=Math.exp(-_*f*A);return o-T*((g+_*f*u)/C*Math.sin(C*A)+u*Math.cos(C*A))}}else if(_===1)S=C=>o-Math.exp(-f*C)*(u+(g+f*u)*C);else{let C=f*Math.sqrt(_*_-1);S=A=>{let T=Math.exp(-_*f*A),P=Math.min(C*A,300);return o-T*((g+_*f*u)*Math.sinh(P)+C*u*Math.cosh(P))/C}}let E={calculatedDuration:m&&d||null,next:C=>{let A=S(C);if(m)a.done=C>=d;else{let T=0;_<1&&(T=C===0?ge(g):Qs(S,C,A));let P=Math.abs(T)<=i,G=Math.abs(o-A)<=s;a.done=P&&G}return a.value=a.done?o:A,a},toString:()=>{let C=Math.min(bi(E),2e4),A=Zs(T=>E.next(C*T).value,C,30);return C+"ms "+A}};return E}function Gc(n,t=100,e){let i=e({...n,keyframes:[0,t]}),s=Math.min(bi(i),2e4);return{type:"keyframes",ease:r=>i.next(s*r).value/t,duration:Te(s)}}function Rn(n){return typeof n=="function"}var Wc=(n,t,e)=>{let i=t-n;return((e-n)%i+i)%i+n};var tr=n=>Array.isArray(n)&&typeof n[0]!="number";function er(n,t){return tr(n)?n[Wc(0,n.length,t)]:n}var en=(n,t,e)=>n+(t-n)*e;function nr(n,t){let e=n[n.length-1];for(let i=1;i<=t;i++){let s=Cn(0,t,i);n.push(en(e,1,s))}}function ir(n){let t=[0];return nr(t,n.length-1),t}var ee=n=>!!(n&&n.getVelocity);function sr(n,t,e){var i;if(n instanceof Element)return[n];if(typeof n=="string"){let s=document;t&&(s=t.current);let r=(i=e==null?void 0:e[n])!==null&&i!==void 0?i:s.querySelectorAll(n);return r?Array.from(r):[]}return Array.from(n)}function _s(n){return typeof n=="object"&&!Array.isArray(n)}function rr(n,t,e,i){return typeof n=="string"&&_s(t)?sr(n,e,i):n instanceof NodeList?Array.from(n):Array.isArray(n)?n:[n]}function Xc(n,t,e){return n*(t+1)}function ia(n,t,e,i){var s;return typeof t=="number"?t:t.startsWith("-")||t.startsWith("+")?Math.max(0,n+parseFloat(t)):t==="<"?e:(s=i.get(t))!==null&&s!==void 0?s:n}function qc(n,t){n.indexOf(t)===-1&&n.push(t)}function or(n,t){let e=n.indexOf(t);e>-1&&n.splice(e,1)}function vd(n,t,e){for(let i=0;i<n.length;i++){let s=n[i];s.at>t&&s.at<e&&(or(n,s),i--)}}function Yc(n,t,e,i,s,r){vd(n,s,r);for(let o=0;o<t.length;o++)n.push({value:t[o],at:en(s,r,i[o]),easing:er(e,o)})}function Kc(n,t){for(let e=0;e<n.length;e++)n[e]=n[e]/(t+1)}function $c(n,t){return n.at===t.at?n.value===null?1:t.value===null?-1:0:n.at-t.at}var yd="easeInOut",Md=20;function Qc(n,{defaultTransition:t={},...e}={},i,s){let r=t.duration||.3,o=new Map,a=new Map,l={},c=new Map,h=0,d=0,p=0;for(let m=0;m<n.length;m++){let g=n[m];if(typeof g=="string"){c.set(g,d);continue}else if(!Array.isArray(g)){c.set(g.name,ia(d,g.at,h,c));continue}let[_,u,f={}]=g;f.at!==void 0&&(d=ia(d,f.at,h,c));let b=0,S=(E,C,A,T=0,P=0)=>{let G=Sd(E),{delay:x=0,times:y=ir(G),type:V="keyframes",repeat:U,repeatType:H,repeatDelay:$=0,...k}=C,{ease:Z=t.ease||"easeOut",duration:z}=C,it=typeof x=="function"?x(T,P):x,ot=G.length,pt=Rn(V)?V:s==null?void 0:s[V];if(ot<=2&&pt){let J=100;if(ot===2&&wd(G)){let bt=G[1]-G[0];J=Math.abs(bt)}let ht={...k};z!==void 0&&(ht.duration=ge(z));let rt=Gc(ht,J,pt);Z=rt.ease,z=rt.duration}z!=null||(z=r);let Bt=d+it;y.length===1&&y[0]===0&&(y[1]=1);let Vt=y.length-G.length;if(Vt>0&&nr(y,Vt),G.length===1&&G.unshift(null),U){Ye(U<Md,"Repeat count too high, must be less than 20"),z=Xc(z,U);let J=[...G],ht=[...y];Z=Array.isArray(Z)?[...Z]:[Z];let rt=[...Z];for(let bt=0;bt<U;bt++){G.push(...J);for(let et=0;et<J.length;et++)y.push(ht[et]+(bt+1)),Z.push(et===0?"linear":er(rt,et-1))}Kc(y,U)}let X=Bt+z;Yc(A,G,Z,y,Bt,X),b=Math.max(it+z,b),p=Math.max(X,p)};if(ee(_)){let E=Zc(_,a);S(u,f,Jc("default",E))}else{let E=rr(_,u,i,l),C=E.length;for(let A=0;A<C;A++){u=u,f=f;let T=E[A],P=Zc(T,a);for(let G in u)S(u[G],bd(f,G),Jc(G,P),A,C)}}h=d,d+=b}return a.forEach((m,g)=>{for(let _ in m){let u=m[_];u.sort($c);let f=[],b=[],S=[];for(let C=0;C<u.length;C++){let{at:A,value:T,easing:P}=u[C];f.push(T),b.push(Cn(0,p,A)),S.push(P||"easeOut")}b[0]!==0&&(b.unshift(0),f.unshift(f[0]),S.unshift(yd)),b[b.length-1]!==1&&(b.push(1),f.push(null)),o.has(g)||o.set(g,{keyframes:{},transition:{}});let E=o.get(g);E.keyframes[_]=f,E.transition[_]={...t,duration:p,ease:S,times:b,...e}}}),o}function Zc(n,t){return!t.has(n)&&t.set(n,{}),t.get(n)}function Jc(n,t){return t[n]||(t[n]=[]),t[n]}function Sd(n){return Array.isArray(n)?n:[n]}function bd(n,t){return n&&n[t]?{...n,...n[t]}:{...n}}var Ed=n=>typeof n=="number",wd=n=>n.every(Ed);var un=new WeakMap;function ar(n,t){return n?n[t]||n.default||n:void 0}var nn=["transformPerspective","x","y","z","translateX","translateY","translateZ","scale","scaleX","scaleY","rotate","rotateX","rotateY","rotateZ","skew","skewX","skewY"],ze=new Set(nn);var lr=new Set(["width","height","top","left","right","bottom",...nn]);var jc=n=>Array.isArray(n);var th=n=>jc(n)?n[n.length-1]||0:n;var wi={skipAnimations:!1,useManualTiming:!1};function eh(n){let t=new Set,e=new Set,i=!1,s=!1,r=new WeakSet,o={delta:0,timestamp:0,isProcessing:!1};function a(c){r.has(c)&&(l.schedule(c),n()),c(o)}let l={schedule:(c,h=!1,d=!1)=>{let m=d&&i?t:e;return h&&r.add(c),m.has(c)||m.add(c),c},cancel:c=>{e.delete(c),r.delete(c)},process:c=>{if(o=c,i){s=!0;return}i=!0,[t,e]=[e,t],t.forEach(a),t.clear(),i=!1,s&&(s=!1,l.process(c))}};return l}var cr=["read","resolveKeyframes","update","preRender","render","postRender"],Td=40;function nh(n,t){let e=!1,i=!0,s={delta:0,timestamp:0,isProcessing:!1},r=()=>e=!0,o=cr.reduce((f,b)=>(f[b]=eh(r),f),{}),{read:a,resolveKeyframes:l,update:c,preRender:h,render:d,postRender:p}=o,m=()=>{let f=wi.useManualTiming?s.timestamp:performance.now();e=!1,s.delta=i?1e3/60:Math.max(Math.min(f-s.timestamp,Td),1),s.timestamp=f,s.isProcessing=!0,a.process(s),l.process(s),c.process(s),h.process(s),d.process(s),p.process(s),s.isProcessing=!1,e&&t&&(i=!1,n(m))},g=()=>{e=!0,i=!0,s.isProcessing||n(m)};return{schedule:cr.reduce((f,b)=>{let S=o[b];return f[b]=(E,C=!1,A=!1)=>(e||g(),S.schedule(E,C,A)),f},{}),cancel:f=>{for(let b=0;b<cr.length;b++)o[cr[b]].cancel(f)},state:s,steps:o}}var{schedule:ve,cancel:vs,state:Ti,steps:HM}=nh(typeof requestAnimationFrame!="undefined"?requestAnimationFrame:we,!0);var hr;function Ad(){hr=void 0}var ke={now:()=>(hr===void 0&&ke.set(Ti.isProcessing||wi.useManualTiming?Ti.timestamp:performance.now()),hr),set:n=>{hr=n,queueMicrotask(Ad)}};var Ai=class{constructor(){this.subscriptions=[]}add(t){return qc(this.subscriptions,t),()=>or(this.subscriptions,t)}notify(t,e,i){let s=this.subscriptions.length;if(s)if(s===1)this.subscriptions[0](t,e,i);else for(let r=0;r<s;r++){let o=this.subscriptions[r];o&&o(t,e,i)}}getSize(){return this.subscriptions.length}clear(){this.subscriptions.length=0}};var ih=30,Cd=n=>!isNaN(parseFloat(n)),sh={current:void 0},sa=class{constructor(t,e={}){this.version="11.18.2",this.canTrackVelocity=null,this.events={},this.updateAndNotify=(i,s=!0)=>{let r=ke.now();this.updatedAt!==r&&this.setPrevFrameValue(),this.prev=this.current,this.setCurrent(i),this.current!==this.prev&&this.events.change&&this.events.change.notify(this.current),s&&this.events.renderRequest&&this.events.renderRequest.notify(this.current)},this.hasAnimated=!1,this.setCurrent(t),this.owner=e.owner}setCurrent(t){this.current=t,this.updatedAt=ke.now(),this.canTrackVelocity===null&&t!==void 0&&(this.canTrackVelocity=Cd(this.current))}setPrevFrameValue(t=this.current){this.prevFrameValue=t,this.prevUpdatedAt=this.updatedAt}onChange(t){return this.on("change",t)}on(t,e){this.events[t]||(this.events[t]=new Ai);let i=this.events[t].add(e);return t==="change"?()=>{i(),ve.read(()=>{this.events.change.getSize()||this.stop()})}:i}clearListeners(){for(let t in this.events)this.events[t].clear()}attach(t,e){this.passiveEffect=t,this.stopPassiveEffect=e}set(t,e=!0){!e||!this.passiveEffect?this.updateAndNotify(t,e):this.passiveEffect(t,this.updateAndNotify)}setWithVelocity(t,e,i){this.set(e),this.prev=void 0,this.prevFrameValue=t,this.prevUpdatedAt=this.updatedAt-i}jump(t,e=!0){this.updateAndNotify(t),this.prev=t,this.prevUpdatedAt=this.prevFrameValue=void 0,e&&this.stop(),this.stopPassiveEffect&&this.stopPassiveEffect()}get(){return sh.current&&sh.current.push(this),this.current}getPrevious(){return this.prev}getVelocity(){let t=ke.now();if(!this.canTrackVelocity||this.prevFrameValue===void 0||t-this.updatedAt>ih)return 0;let e=Math.min(this.updatedAt-this.prevUpdatedAt,ih);return Js(parseFloat(this.current)-parseFloat(this.prevFrameValue),e)}start(t){return this.stop(),new Promise(e=>{this.hasAnimated=!0,this.animation=t(e),this.events.animationStart&&this.events.animationStart.notify()}).then(()=>{this.events.animationComplete&&this.events.animationComplete.notify(),this.clearAnimation()})}stop(){this.animation&&(this.animation.stop(),this.events.animationCancel&&this.events.animationCancel.notify()),this.clearAnimation()}isAnimating(){return!!this.animation}clearAnimation(){delete this.animation}destroy(){this.clearListeners(),this.stop(),this.stopPassiveEffect&&this.stopPassiveEffect()}};function fn(n,t){return new sa(n,t)}function rh(n){let t=[{},{}];return n==null||n.values.forEach((e,i)=>{t[0][i]=e.get(),t[1][i]=e.getVelocity()}),t}function ur(n,t,e,i){if(typeof t=="function"){let[s,r]=rh(i);t=t(e!==void 0?e:n.custom,s,r)}if(typeof t=="string"&&(t=n.variants&&n.variants[t]),typeof t=="function"){let[s,r]=rh(i);t=t(e!==void 0?e:n.custom,s,r)}return t}function oh(n,t,e){let i=n.getProps();return ur(i,t,e!==void 0?e:i.custom,n)}function Rd(n,t,e){n.hasValue(t)?n.getValue(t).set(e):n.addValue(t,fn(e))}function ah(n,t){let e=oh(n,t),{transitionEnd:i={},transition:s={},...r}=e||{};r={...r,...i};for(let o in r){let a=th(r[o]);Rd(n,o,a)}}function lh(n){return!!(ee(n)&&n.add)}function ch(n,t){let e=n.getValue("willChange");if(lh(e))return e.add(t)}var Ci=n=>n.replace(/([a-z])([A-Z])/gu,"$1-$2").toLowerCase();var Pd="framerAppearId",hh="data-"+Ci(Pd);function uh(n){return n.props[hh]}var fr={current:!1};function ra(n,t){n.timeline=t,n.onfinish=null}var ys=n=>Array.isArray(n)&&typeof n[0]=="number";var fh={linearEasing:void 0};function dh(n,t){let e=Mi(n);return()=>{var i;return(i=fh[t])!==null&&i!==void 0?i:e()}}var Ri=dh(()=>{try{document.createElement("div").animate({opacity:0},{easing:"linear(0, 1)"})}catch{return!1}return!0},"linearEasing");function aa(n){return!!(typeof n=="function"&&Ri()||!n||typeof n=="string"&&(n in oa||Ri())||ys(n)||Array.isArray(n)&&n.every(aa))}var Ms=([n,t,e,i])=>`cubic-bezier(${n}, ${t}, ${e}, ${i})`,oa={linear:"linear",ease:"ease",easeIn:"ease-in",easeOut:"ease-out",easeInOut:"ease-in-out",circIn:Ms([0,.65,.55,1]),circOut:Ms([.55,0,1,.45]),backIn:Ms([.31,.01,.66,-.59]),backOut:Ms([.33,1.53,.69,.99])};function la(n,t){if(n)return typeof n=="function"&&Ri()?Zs(n,t):ys(n)?Ms(n):Array.isArray(n)?n.map(e=>la(e,t)||oa.easeOut):oa[n]}var ph=(n,t,e)=>(((1-3*e+3*t)*n+(3*e-6*t))*n+3*t)*n,Id=1e-7,Ld=12;function Dd(n,t,e,i,s){let r,o,a=0;do o=t+(e-t)/2,r=ph(o,i,s)-n,r>0?e=o:t=o;while(Math.abs(r)>Id&&++a<Ld);return o}function Pn(n,t,e,i){if(n===t&&e===i)return we;let s=r=>Dd(r,0,1,n,e);return r=>r===0||r===1?r:ph(s(r),t,i)}var dr=n=>t=>t<=.5?n(2*t)/2:(2-n(2*(1-t)))/2;var pr=n=>t=>1-n(1-t);var ca=Pn(.33,1.53,.69,.99),Ss=pr(ca),mr=dr(Ss);var gr=n=>(n*=2)<1?.5*Ss(n):.5*(2-Math.pow(2,-10*(n-1)));var xr=n=>1-Math.sin(Math.acos(n)),mh=pr(xr),_r=dr(xr);var vr=n=>/^0[^.\s]+$/u.test(n);function gh(n){return typeof n=="number"?n===0:n!==null?n==="none"||n==="0"||vr(n):!0}var sn={test:n=>typeof n=="number",parse:parseFloat,transform:n=>n},dn={...sn,transform:n=>De(0,1,n)},bs={...sn,default:1};var In=n=>Math.round(n*1e5)/1e5;var Pi=/-?(?:\d+(?:\.\d+)?|\.\d+)/gu;function xh(n){return n==null}var _h=/^(?:#[\da-f]{3,8}|(?:rgb|hsl)a?\((?:-?[\d.]+%?[,\s]+){2}-?[\d.]+%?\s*(?:[,/]\s*)?(?:\b\d+(?:\.\d+)?|\.\d+)?%?\))$/iu;var Ii=(n,t)=>e=>!!(typeof e=="string"&&_h.test(e)&&e.startsWith(n)||t&&!xh(e)&&Object.prototype.hasOwnProperty.call(e,t)),yr=(n,t,e)=>i=>{if(typeof i!="string")return i;let[s,r,o,a]=i.match(Pi);return{[n]:parseFloat(s),[t]:parseFloat(r),[e]:parseFloat(o),alpha:a!==void 0?parseFloat(a):1}};var Ud=n=>De(0,255,n),ha={...sn,transform:n=>Math.round(Ud(n))},rn={test:Ii("rgb","red"),parse:yr("red","green","blue"),transform:({red:n,green:t,blue:e,alpha:i=1})=>"rgba("+ha.transform(n)+", "+ha.transform(t)+", "+ha.transform(e)+", "+In(dn.transform(i))+")"};function Nd(n){let t="",e="",i="",s="";return n.length>5?(t=n.substring(1,3),e=n.substring(3,5),i=n.substring(5,7),s=n.substring(7,9)):(t=n.substring(1,2),e=n.substring(2,3),i=n.substring(3,4),s=n.substring(4,5),t+=t,e+=e,i+=i,s+=s),{red:parseInt(t,16),green:parseInt(e,16),blue:parseInt(i,16),alpha:s?parseInt(s,16)/255:1}}var Es={test:Ii("#"),parse:Nd,transform:rn.transform};var ws=n=>({test:t=>typeof t=="string"&&t.endsWith(n)&&t.split(" ").length===1,parse:parseFloat,transform:t=>`${t}${n}`}),on=ws("deg"),Ln=ws("%"),St=ws("px"),vh=ws("vh"),yh=ws("vw"),ua={...Ln,parse:n=>Ln.parse(n)/100,transform:n=>Ln.transform(n*100)};var Dn={test:Ii("hsl","hue"),parse:yr("hue","saturation","lightness"),transform:({hue:n,saturation:t,lightness:e,alpha:i=1})=>"hsla("+Math.round(n)+", "+Ln.transform(In(t))+", "+Ln.transform(In(e))+", "+In(dn.transform(i))+")"};var he={test:n=>rn.test(n)||Es.test(n)||Dn.test(n),parse:n=>rn.test(n)?rn.parse(n):Dn.test(n)?Dn.parse(n):Es.parse(n),transform:n=>typeof n=="string"?n:n.hasOwnProperty("red")?rn.transform(n):Dn.transform(n)};var Mh=/(?:#[\da-f]{3,8}|(?:rgb|hsl)a?\((?:-?[\d.]+%?[,\s]+){2}-?[\d.]+%?\s*(?:[,/]\s*)?(?:\b\d+(?:\.\d+)?|\.\d+)?%?\))/giu;function Fd(n){var t,e;return isNaN(n)&&typeof n=="string"&&(((t=n.match(Pi))===null||t===void 0?void 0:t.length)||0)+(((e=n.match(Mh))===null||e===void 0?void 0:e.length)||0)>0}var bh="number",Eh="color",Od="var",Bd="var(",Sh="${}",Vd=/var\s*\(\s*--(?:[\w-]+\s*|[\w-]+\s*,(?:\s*[^)(\s]|\s*\((?:[^)(]|\([^)(]*\))*\))+\s*)\)|#[\da-f]{3,8}|(?:rgb|hsl)a?\((?:-?[\d.]+%?[,\s]+){2}-?[\d.]+%?\s*(?:[,/]\s*)?(?:\b\d+(?:\.\d+)?|\.\d+)?%?\)|-?(?:\d+(?:\.\d+)?|\.\d+)/giu;function Jn(n){let t=n.toString(),e=[],i={color:[],number:[],var:[]},s=[],r=0,a=t.replace(Vd,l=>(he.test(l)?(i.color.push(r),s.push(Eh),e.push(he.parse(l))):l.startsWith(Bd)?(i.var.push(r),s.push(Od),e.push(l)):(i.number.push(r),s.push(bh),e.push(parseFloat(l))),++r,Sh)).split(Sh);return{values:e,split:a,indexes:i,types:s}}function wh(n){return Jn(n).values}function Th(n){let{split:t,types:e}=Jn(n),i=t.length;return s=>{let r="";for(let o=0;o<i;o++)if(r+=t[o],s[o]!==void 0){let a=e[o];a===bh?r+=In(s[o]):a===Eh?r+=he.transform(s[o]):r+=s[o]}return r}}var zd=n=>typeof n=="number"?0:n;function kd(n){let t=wh(n);return Th(n)(t.map(zd))}var He={test:Fd,parse:wh,createTransformer:Th,getAnimatableNone:kd};var Hd=new Set(["brightness","contrast","saturate","opacity"]);function Gd(n){let[t,e]=n.slice(0,-1).split("(");if(t==="drop-shadow")return n;let[i]=e.match(Pi)||[];if(!i)return n;let s=e.replace(i,""),r=Hd.has(t)?1:0;return i!==e&&(r*=100),t+"("+r+s+")"}var Wd=/\b([a-z-]*)\(.*?\)/gu,Ts={...He,getAnimatableNone:n=>{let t=n.match(Wd);return t?t.map(Gd).join(" "):n}};var Ah={borderWidth:St,borderTopWidth:St,borderRightWidth:St,borderBottomWidth:St,borderLeftWidth:St,borderRadius:St,radius:St,borderTopLeftRadius:St,borderTopRightRadius:St,borderBottomRightRadius:St,borderBottomLeftRadius:St,width:St,maxWidth:St,height:St,maxHeight:St,top:St,right:St,bottom:St,left:St,padding:St,paddingTop:St,paddingRight:St,paddingBottom:St,paddingLeft:St,margin:St,marginTop:St,marginRight:St,marginBottom:St,marginLeft:St,backgroundPositionX:St,backgroundPositionY:St};var Ch={rotate:on,rotateX:on,rotateY:on,rotateZ:on,scale:bs,scaleX:bs,scaleY:bs,scaleZ:bs,skew:on,skewX:on,skewY:on,distance:St,translateX:St,translateY:St,translateZ:St,x:St,y:St,z:St,perspective:St,transformPerspective:St,opacity:dn,originX:ua,originY:ua,originZ:St};var fa={...sn,transform:Math.round};var Li={...Ah,...Ch,zIndex:fa,size:St,fillOpacity:dn,strokeOpacity:dn,numOctaves:fa};var Xd={...Li,color:he,backgroundColor:he,outlineColor:he,fill:he,stroke:he,borderColor:he,borderTopColor:he,borderRightColor:he,borderBottomColor:he,borderLeftColor:he,filter:Ts,WebkitFilter:Ts},Di=n=>Xd[n];function Mr(n,t){let e=Di(n);return e!==Ts&&(e=He),e.getAnimatableNone?e.getAnimatableNone(t):void 0}var qd=new Set(["auto","none","0"]);function Rh(n,t,e){let i=0,s;for(;i<n.length&&!s;){let r=n[i];typeof r=="string"&&!qd.has(r)&&Jn(r).values.length&&(s=n[i]),i++}if(s&&e)for(let r of t)n[r]=Mr(e,s)}var da=n=>n===sn||n===St,Ph=(n,t)=>parseFloat(n.split(", ")[t]),Ih=(n,t)=>(e,{transform:i})=>{if(i==="none"||!i)return 0;let s=i.match(/^matrix3d\((.+)\)$/u);if(s)return Ph(s[1],t);{let r=i.match(/^matrix\((.+)\)$/u);return r?Ph(r[1],n):0}},Yd=new Set(["x","y","z"]),Kd=nn.filter(n=>!Yd.has(n));function Lh(n){let t=[];return Kd.forEach(e=>{let i=n.getValue(e);i!==void 0&&(t.push([e,i.get()]),i.set(e.startsWith("scale")?1:0))}),t}var Qn={width:({x:n},{paddingLeft:t="0",paddingRight:e="0"})=>n.max-n.min-parseFloat(t)-parseFloat(e),height:({y:n},{paddingTop:t="0",paddingBottom:e="0"})=>n.max-n.min-parseFloat(t)-parseFloat(e),top:(n,{top:t})=>parseFloat(t),left:(n,{left:t})=>parseFloat(t),bottom:({y:n},{top:t})=>parseFloat(t)+(n.max-n.min),right:({x:n},{left:t})=>parseFloat(t)+(n.max-n.min),x:Ih(4,13),y:Ih(5,14)};Qn.translateX=Qn.x;Qn.translateY=Qn.y;var jn=new Set,pa=!1,ma=!1;function Dh(){if(ma){let n=Array.from(jn).filter(i=>i.needsMeasurement),t=new Set(n.map(i=>i.element)),e=new Map;t.forEach(i=>{let s=Lh(i);s.length&&(e.set(i,s),i.render())}),n.forEach(i=>i.measureInitialState()),t.forEach(i=>{i.render();let s=e.get(i);s&&s.forEach(([r,o])=>{var a;(a=i.getValue(r))===null||a===void 0||a.set(o)})}),n.forEach(i=>i.measureEndState()),n.forEach(i=>{i.suspendedScrollY!==void 0&&window.scrollTo(0,i.suspendedScrollY)})}ma=!1,pa=!1,jn.forEach(n=>n.complete()),jn.clear()}function Uh(){jn.forEach(n=>{n.readKeyframes(),n.needsMeasurement&&(ma=!0)})}function Nh(){Uh(),Dh()}var Un=class{constructor(t,e,i,s,r,o=!1){this.isComplete=!1,this.isAsync=!1,this.needsMeasurement=!1,this.isScheduled=!1,this.unresolvedKeyframes=[...t],this.onComplete=e,this.name=i,this.motionValue=s,this.element=r,this.isAsync=o}scheduleResolve(){this.isScheduled=!0,this.isAsync?(jn.add(this),pa||(pa=!0,ve.read(Uh),ve.resolveKeyframes(Dh))):(this.readKeyframes(),this.complete())}readKeyframes(){let{unresolvedKeyframes:t,name:e,element:i,motionValue:s}=this;for(let r=0;r<t.length;r++)if(t[r]===null)if(r===0){let o=s==null?void 0:s.get(),a=t[t.length-1];if(o!==void 0)t[0]=o;else if(i&&e){let l=i.readValue(e,a);l!=null&&(t[0]=l)}t[0]===void 0&&(t[0]=a),s&&o===void 0&&s.set(t[0])}else t[r]=t[r-1]}setFinalKeyframe(){}measureInitialState(){}renderEndStyles(){}measureEndState(){}complete(){this.isComplete=!0,this.onComplete(this.unresolvedKeyframes,this.finalKeyframe),jn.delete(this)}cancel(){this.isComplete||(this.isScheduled=!1,jn.delete(this))}resume(){this.isComplete||this.scheduleResolve()}};var Sr=n=>/^-?(?:\d+(?:\.\d+)?|\.\d+)$/u.test(n);var Fh=n=>t=>typeof t=="string"&&t.startsWith(n),br=Fh("--"),$d=Fh("var(--"),Ui=n=>$d(n)?Zd.test(n.split("/*")[0].trim()):!1,Zd=/var\(--(?:[\w-]+\s*|[\w-]+\s*,(?:\s*[^)(\s]|\s*\((?:[^)(]|\([^)(]*\))*\))+\s*)\)$/iu;var Jd=/^var\(--(?:([\w-]+)|([\w-]+), ?([a-zA-Z\d ()%#.,-]+))\)/u;function Qd(n){let t=Jd.exec(n);if(!t)return[,];let[,e,i,s]=t;return[`--${e!=null?e:i}`,s]}var jd=4;function ga(n,t,e=1){Ye(e<=jd,`Max CSS variable fallback depth detected in property "${n}". This may indicate a circular fallback dependency.`);let[i,s]=Qd(n);if(!i)return;let r=window.getComputedStyle(t).getPropertyValue(i);if(r){let o=r.trim();return Sr(o)?parseFloat(o):o}return Ui(s)?ga(s,t,e+1):s}var Er=n=>t=>t.test(n);var Oh={test:n=>n==="auto",parse:n=>n};var xa=[sn,St,Ln,on,yh,vh,Oh],_a=n=>xa.find(Er(n));var Ni=class extends Un{constructor(t,e,i,s,r){super(t,e,i,s,r,!0)}readKeyframes(){let{unresolvedKeyframes:t,element:e,name:i}=this;if(!e||!e.current)return;super.readKeyframes();for(let l=0;l<t.length;l++){let c=t[l];if(typeof c=="string"&&(c=c.trim(),Ui(c))){let h=ga(c,e.current);h!==void 0&&(t[l]=h),l===t.length-1&&(this.finalKeyframe=c)}}if(this.resolveNoneKeyframes(),!lr.has(i)||t.length!==2)return;let[s,r]=t,o=_a(s),a=_a(r);if(o!==a)if(da(o)&&da(a))for(let l=0;l<t.length;l++){let c=t[l];typeof c=="string"&&(t[l]=parseFloat(c))}else this.needsMeasurement=!0}resolveNoneKeyframes(){let{unresolvedKeyframes:t,name:e}=this,i=[];for(let s=0;s<t.length;s++)gh(t[s])&&i.push(s);i.length&&Rh(t,i,e)}measureInitialState(){let{element:t,unresolvedKeyframes:e,name:i}=this;if(!t||!t.current)return;i==="height"&&(this.suspendedScrollY=window.pageYOffset),this.measuredOrigin=Qn[i](t.measureViewportBox(),window.getComputedStyle(t.current)),e[0]=this.measuredOrigin;let s=e[e.length-1];s!==void 0&&t.getValue(i,s).jump(s,!1)}measureEndState(){var t;let{element:e,name:i,unresolvedKeyframes:s}=this;if(!e||!e.current)return;let r=e.getValue(i);r&&r.jump(this.measuredOrigin,!1);let o=s.length-1,a=s[o];s[o]=Qn[i](e.measureViewportBox(),window.getComputedStyle(e.current)),a!==null&&this.finalKeyframe===void 0&&(this.finalKeyframe=a),!((t=this.removedTransforms)===null||t===void 0)&&t.length&&this.removedTransforms.forEach(([l,c])=>{e.getValue(l).set(c)}),this.resolveNoneKeyframes()}};var va=(n,t)=>t==="zIndex"?!1:!!(typeof n=="number"||Array.isArray(n)||typeof n=="string"&&(He.test(n)||n==="0")&&!n.startsWith("url("));function tp(n){let t=n[0];if(n.length===1)return!0;for(let e=0;e<n.length;e++)if(n[e]!==t)return!0}function Bh(n,t,e,i){let s=n[0];if(s===null)return!1;if(t==="display"||t==="visibility")return!0;let r=n[n.length-1],o=va(s,t),a=va(r,t);return An(o===a,`You are trying to animate ${t} from "${s}" to "${r}". ${s} is not an animatable value - to enable this animation set ${s} to a value animatable to ${r} via the \`style\` property.`),!o||!a?!1:tp(n)||(e==="spring"||Rn(e))&&i}var ep=n=>n!==null;function Nn(n,{repeat:t,repeatType:e="loop"},i){let s=n.filter(ep),r=t&&e!=="loop"&&t%2===1?0:s.length-1;return!r||i===void 0?s[r]:i}var np=40,Fi=class{constructor({autoplay:t=!0,delay:e=0,type:i="keyframes",repeat:s=0,repeatDelay:r=0,repeatType:o="loop",...a}){this.isStopped=!1,this.hasAttemptedResolve=!1,this.createdAt=ke.now(),this.options={autoplay:t,delay:e,type:i,repeat:s,repeatDelay:r,repeatType:o,...a},this.updateFinishedPromise()}calcStartTime(){return this.resolvedAt?this.resolvedAt-this.createdAt>np?this.resolvedAt:this.createdAt:this.createdAt}get resolved(){return!this._resolved&&!this.hasAttemptedResolve&&Nh(),this._resolved}onKeyframesResolved(t,e){this.resolvedAt=ke.now(),this.hasAttemptedResolve=!0;let{name:i,type:s,velocity:r,delay:o,onComplete:a,onUpdate:l,isGenerator:c}=this.options;if(!c&&!Bh(t,i,s,r))if(fr.current||!o){l&&l(Nn(t,this.options,e)),a&&a(),this.resolveFinishedPromise();return}else this.options.duration=0;let h=this.initPlayback(t,e);h!==!1&&(this._resolved={keyframes:t,finalKeyframe:e,...h},this.onPostResolved())}onPostResolved(){}then(t,e){return this.currentFinishedPromise.then(t,e)}flatten(){this.options.type="keyframes",this.options.ease="linear"}updateFinishedPromise(){this.currentFinishedPromise=new Promise(t=>{this.resolveFinishedPromise=t})}};function ya(n,t,e){return e<0&&(e+=1),e>1&&(e-=1),e<1/6?n+(t-n)*6*e:e<1/2?t:e<2/3?n+(t-n)*(2/3-e)*6:n}function Vh({hue:n,saturation:t,lightness:e,alpha:i}){n/=360,t/=100,e/=100;let s=0,r=0,o=0;if(!t)s=r=o=e;else{let a=e<.5?e*(1+t):e+t-e*t,l=2*e-a;s=ya(l,a,n+1/3),r=ya(l,a,n),o=ya(l,a,n-1/3)}return{red:Math.round(s*255),green:Math.round(r*255),blue:Math.round(o*255),alpha:i}}function Oi(n,t){return e=>e>0?t:n}var Ma=(n,t,e)=>{let i=n*n,s=e*(t*t-i)+i;return s<0?0:Math.sqrt(s)},ip=[Es,rn,Dn],sp=n=>ip.find(t=>t.test(n));function zh(n){let t=sp(n);if(An(!!t,`'${n}' is not an animatable color. Use the equivalent color code instead.`),!t)return!1;let e=t.parse(n);return t===Dn&&(e=Vh(e)),e}var Sa=(n,t)=>{let e=zh(n),i=zh(t);if(!e||!i)return Oi(n,t);let s={...e};return r=>(s.red=Ma(e.red,i.red,r),s.green=Ma(e.green,i.green,r),s.blue=Ma(e.blue,i.blue,r),s.alpha=en(e.alpha,i.alpha,r),rn.transform(s))};var rp=(n,t)=>e=>t(n(e)),Bi=(...n)=>n.reduce(rp);var wr=new Set(["none","hidden"]);function kh(n,t){return wr.has(n)?e=>e<=0?n:t:e=>e>=1?t:n}function op(n,t){return e=>en(n,t,e)}function Tr(n){return typeof n=="number"?op:typeof n=="string"?Ui(n)?Oi:he.test(n)?Sa:cp:Array.isArray(n)?Hh:typeof n=="object"?he.test(n)?Sa:ap:Oi}function Hh(n,t){let e=[...n],i=e.length,s=n.map((r,o)=>Tr(r)(r,t[o]));return r=>{for(let o=0;o<i;o++)e[o]=s[o](r);return e}}function ap(n,t){let e={...n,...t},i={};for(let s in e)n[s]!==void 0&&t[s]!==void 0&&(i[s]=Tr(n[s])(n[s],t[s]));return s=>{for(let r in i)e[r]=i[r](s);return e}}function lp(n,t){var e;let i=[],s={color:0,var:0,number:0};for(let r=0;r<t.values.length;r++){let o=t.types[r],a=n.indexes[o][s[o]],l=(e=n.values[a])!==null&&e!==void 0?e:0;i[r]=l,s[o]++}return i}var cp=(n,t)=>{let e=He.createTransformer(t),i=Jn(n),s=Jn(t);return i.indexes.var.length===s.indexes.var.length&&i.indexes.color.length===s.indexes.color.length&&i.indexes.number.length>=s.indexes.number.length?wr.has(n)&&!s.values.length||wr.has(t)&&!i.values.length?kh(n,t):Bi(Hh(lp(i,s),s.values),e):(An(!0,`Complex values '${n}' and '${t}' too different to mix. Ensure all colors are of the same type, and that each contains the same quantity of number and color values. Falling back to instant transition.`),Oi(n,t))};function Ar(n,t,e){return typeof n=="number"&&typeof t=="number"&&typeof e=="number"?en(n,t,e):Tr(n)(n,t)}function ba({keyframes:n,velocity:t=0,power:e=.8,timeConstant:i=325,bounceDamping:s=10,bounceStiffness:r=500,modifyTarget:o,min:a,max:l,restDelta:c=.5,restSpeed:h}){let d=n[0],p={done:!1,value:d},m=P=>a!==void 0&&P<a||l!==void 0&&P>l,g=P=>a===void 0?l:l===void 0||Math.abs(a-P)<Math.abs(l-P)?a:l,_=e*t,u=d+_,f=o===void 0?u:o(u);f!==u&&(_=f-d);let b=P=>-_*Math.exp(-P/i),S=P=>f+b(P),E=P=>{let G=b(P),x=S(P);p.done=Math.abs(G)<=c,p.value=p.done?f:x},C,A,T=P=>{m(p.value)&&(C=P,A=Ei({keyframes:[p.value,g(p.value)],velocity:Qs(S,P,p.value),damping:s,stiffness:r,restDelta:c,restSpeed:h}))};return T(0),{calculatedDuration:null,next:P=>{let G=!1;return!A&&C===void 0&&(G=!0,E(P),T(P)),C!==void 0&&P>=C?A.next(P-C):(!G&&E(P),p)}}}var Gh=Pn(.42,0,1,1),Wh=Pn(0,0,.58,1),Cr=Pn(.42,0,.58,1);var Xh={linear:we,easeIn:Gh,easeInOut:Cr,easeOut:Wh,circIn:xr,circInOut:_r,circOut:mh,backIn:Ss,backInOut:mr,backOut:ca,anticipate:gr},As=n=>{if(ys(n)){Ye(n.length===4,"Cubic bezier arrays must contain four numerical values.");let[t,e,i,s]=n;return Pn(t,e,i,s)}else if(typeof n=="string")return Ye(Xh[n]!==void 0,`Invalid easing type '${n}'`),Xh[n];return n};function hp(n,t,e){let i=[],s=e||Ar,r=n.length-1;for(let o=0;o<r;o++){let a=s(n[o],n[o+1]);if(t){let l=Array.isArray(t)?t[o]||we:t;a=Bi(l,a)}i.push(a)}return i}function qh(n,t,{clamp:e=!0,ease:i,mixer:s}={}){let r=n.length;if(Ye(r===t.length,"Both input and output ranges must be the same length"),r===1)return()=>t[0];if(r===2&&t[0]===t[1])return()=>t[1];let o=n[0]===n[1];n[0]>n[r-1]&&(n=[...n].reverse(),t=[...t].reverse());let a=hp(t,i,s),l=a.length,c=h=>{if(o&&h<n[0])return t[0];let d=0;if(l>1)for(;d<n.length-2&&!(h<n[d+1]);d++);let p=Cn(n[d],n[d+1],h);return a[d](p)};return e?h=>c(De(n[0],n[r-1],h)):c}function Yh(n,t){return n.map(e=>e*t)}function up(n,t){return n.map(()=>t||Cr).splice(0,n.length-1)}function Cs({duration:n=300,keyframes:t,times:e,ease:i="easeInOut"}){let s=tr(i)?i.map(As):As(i),r={done:!1,value:t[0]},o=Yh(e&&e.length===t.length?e:ir(t),n),a=qh(o,t,{ease:Array.isArray(s)?s:up(t,s)});return{calculatedDuration:n,next:l=>(r.value=a(l),r.done=l>=n,r)}}var Kh=n=>{let t=({timestamp:e})=>n(e);return{start:()=>ve.update(t,!0),stop:()=>vs(t),now:()=>Ti.isProcessing?Ti.timestamp:ke.now()}};var fp={decay:ba,inertia:ba,tween:Cs,keyframes:Cs,spring:Ei},dp=n=>n/100,ti=class extends Fi{constructor(t){super(t),this.holdTime=null,this.cancelTime=null,this.currentTime=0,this.playbackSpeed=1,this.pendingPlayState="running",this.startTime=null,this.state="idle",this.stop=()=>{if(this.resolver.cancel(),this.isStopped=!0,this.state==="idle")return;this.teardown();let{onStop:l}=this.options;l&&l()};let{name:e,motionValue:i,element:s,keyframes:r}=this.options,o=(s==null?void 0:s.KeyframeResolver)||Un,a=(l,c)=>this.onKeyframesResolved(l,c);this.resolver=new o(r,a,e,i,s),this.resolver.scheduleResolve()}flatten(){super.flatten(),this._resolved&&Object.assign(this._resolved,this.initPlayback(this._resolved.keyframes))}initPlayback(t){let{type:e="keyframes",repeat:i=0,repeatDelay:s=0,repeatType:r,velocity:o=0}=this.options,a=Rn(e)?e:fp[e]||Cs,l,c;a!==Cs&&typeof t[0]!="number"&&(l=Bi(dp,Ar(t[0],t[1])),t=[0,100]);let h=a({...this.options,keyframes:t});r==="mirror"&&(c=a({...this.options,keyframes:[...t].reverse(),velocity:-o})),h.calculatedDuration===null&&(h.calculatedDuration=bi(h));let{calculatedDuration:d}=h,p=d+s,m=p*(i+1)-s;return{generator:h,mirroredGenerator:c,mapPercentToKeyframes:l,calculatedDuration:d,resolvedDuration:p,totalDuration:m}}onPostResolved(){let{autoplay:t=!0}=this.options;this.play(),this.pendingPlayState==="paused"||!t?this.pause():this.state=this.pendingPlayState}tick(t,e=!1){let{resolved:i}=this;if(!i){let{keyframes:P}=this.options;return{done:!0,value:P[P.length-1]}}let{finalKeyframe:s,generator:r,mirroredGenerator:o,mapPercentToKeyframes:a,keyframes:l,calculatedDuration:c,totalDuration:h,resolvedDuration:d}=i;if(this.startTime===null)return r.next(0);let{delay:p,repeat:m,repeatType:g,repeatDelay:_,onUpdate:u}=this.options;this.speed>0?this.startTime=Math.min(this.startTime,t):this.speed<0&&(this.startTime=Math.min(t-h/this.speed,this.startTime)),e?this.currentTime=t:this.holdTime!==null?this.currentTime=this.holdTime:this.currentTime=Math.round(t-this.startTime)*this.speed;let f=this.currentTime-p*(this.speed>=0?1:-1),b=this.speed>=0?f<0:f>h;this.currentTime=Math.max(f,0),this.state==="finished"&&this.holdTime===null&&(this.currentTime=h);let S=this.currentTime,E=r;if(m){let P=Math.min(this.currentTime,h)/d,G=Math.floor(P),x=P%1;!x&&P>=1&&(x=1),x===1&&G--,G=Math.min(G,m+1),!!(G%2)&&(g==="reverse"?(x=1-x,_&&(x-=_/d)):g==="mirror"&&(E=o)),S=De(0,1,x)*d}let C=b?{done:!1,value:l[0]}:E.next(S);a&&(C.value=a(C.value));let{done:A}=C;!b&&c!==null&&(A=this.speed>=0?this.currentTime>=h:this.currentTime<=0);let T=this.holdTime===null&&(this.state==="finished"||this.state==="running"&&A);return T&&s!==void 0&&(C.value=Nn(l,this.options,s)),u&&u(C.value),T&&this.finish(),C}get duration(){let{resolved:t}=this;return t?Te(t.calculatedDuration):0}get time(){return Te(this.currentTime)}set time(t){t=ge(t),this.currentTime=t,this.holdTime!==null||this.speed===0?this.holdTime=t:this.driver&&(this.startTime=this.driver.now()-t/this.speed)}get speed(){return this.playbackSpeed}set speed(t){let e=this.playbackSpeed!==t;this.playbackSpeed=t,e&&(this.time=Te(this.currentTime))}play(){if(this.resolver.isScheduled||this.resolver.resume(),!this._resolved){this.pendingPlayState="running";return}if(this.isStopped)return;let{driver:t=Kh,onPlay:e,startTime:i}=this.options;this.driver||(this.driver=t(r=>this.tick(r))),e&&e();let s=this.driver.now();this.holdTime!==null?this.startTime=s-this.holdTime:this.startTime?this.state==="finished"&&(this.startTime=s):this.startTime=i!=null?i:this.calcStartTime(),this.state==="finished"&&this.updateFinishedPromise(),this.cancelTime=this.startTime,this.holdTime=null,this.state="running",this.driver.start()}pause(){var t;if(!this._resolved){this.pendingPlayState="paused";return}this.state="paused",this.holdTime=(t=this.currentTime)!==null&&t!==void 0?t:0}complete(){this.state!=="running"&&this.play(),this.pendingPlayState=this.state="finished",this.holdTime=null}finish(){this.teardown(),this.state="finished";let{onComplete:t}=this.options;t&&t()}cancel(){this.cancelTime!==null&&this.tick(this.cancelTime),this.teardown(),this.updateFinishedPromise()}teardown(){this.state="idle",this.stopDriver(),this.resolveFinishedPromise(),this.updateFinishedPromise(),this.startTime=this.cancelTime=null,this.resolver.cancel()}stopDriver(){this.driver&&(this.driver.stop(),this.driver=void 0)}sample(t){return this.startTime=0,this.tick(t,!0)}};var $h=new Set(["opacity","clipPath","filter","transform"]);function Zh(n,t,e,{delay:i=0,duration:s=300,repeat:r=0,repeatType:o="loop",ease:a="easeInOut",times:l}={}){let c={[t]:e};l&&(c.offset=l);let h=la(a,s);return Array.isArray(h)&&(c.easing=h),n.animate(c,{delay:i,duration:s,easing:Array.isArray(h)?"linear":h,fill:"both",iterations:r+1,direction:o==="reverse"?"alternate":"normal"})}var Jh=Mi(()=>Object.hasOwnProperty.call(Element.prototype,"animate"));var Rr=10,pp=2e4;function mp(n){return Rn(n.type)||n.type==="spring"||!aa(n.ease)}function gp(n,t){let e=new ti({...t,keyframes:n,repeat:0,delay:0,isGenerator:!0}),i={done:!1,value:n[0]},s=[],r=0;for(;!i.done&&r<pp;)i=e.sample(r),s.push(i.value),r+=Rr;return{times:void 0,keyframes:s,duration:r-Rr,ease:"linear"}}var Qh={anticipate:gr,backInOut:mr,circInOut:_r};function xp(n){return n in Qh}var Rs=class extends Fi{constructor(t){super(t);let{name:e,motionValue:i,element:s,keyframes:r}=this.options;this.resolver=new Ni(r,(o,a)=>this.onKeyframesResolved(o,a),e,i,s),this.resolver.scheduleResolve()}initPlayback(t,e){let{duration:i=300,times:s,ease:r,type:o,motionValue:a,name:l,startTime:c}=this.options;if(!a.owner||!a.owner.current)return!1;if(typeof r=="string"&&Ri()&&xp(r)&&(r=Qh[r]),mp(this.options)){let{onComplete:d,onUpdate:p,motionValue:m,element:g,..._}=this.options,u=gp(t,_);t=u.keyframes,t.length===1&&(t[1]=t[0]),i=u.duration,s=u.times,r=u.ease,o="keyframes"}let h=Zh(a.owner.current,l,t,{...this.options,duration:i,times:s,ease:r});return h.startTime=c!=null?c:this.calcStartTime(),this.pendingTimeline?(ra(h,this.pendingTimeline),this.pendingTimeline=void 0):h.onfinish=()=>{let{onComplete:d}=this.options;a.set(Nn(t,this.options,e)),d&&d(),this.cancel(),this.resolveFinishedPromise()},{animation:h,duration:i,times:s,type:o,ease:r,keyframes:t}}get duration(){let{resolved:t}=this;if(!t)return 0;let{duration:e}=t;return Te(e)}get time(){let{resolved:t}=this;if(!t)return 0;let{animation:e}=t;return Te(e.currentTime||0)}set time(t){let{resolved:e}=this;if(!e)return;let{animation:i}=e;i.currentTime=ge(t)}get speed(){let{resolved:t}=this;if(!t)return 1;let{animation:e}=t;return e.playbackRate}set speed(t){let{resolved:e}=this;if(!e)return;let{animation:i}=e;i.playbackRate=t}get state(){let{resolved:t}=this;if(!t)return"idle";let{animation:e}=t;return e.playState}get startTime(){let{resolved:t}=this;if(!t)return null;let{animation:e}=t;return e.startTime}attachTimeline(t){if(!this._resolved)this.pendingTimeline=t;else{let{resolved:e}=this;if(!e)return we;let{animation:i}=e;ra(i,t)}return we}play(){if(this.isStopped)return;let{resolved:t}=this;if(!t)return;let{animation:e}=t;e.playState==="finished"&&this.updateFinishedPromise(),e.play()}pause(){let{resolved:t}=this;if(!t)return;let{animation:e}=t;e.pause()}stop(){if(this.resolver.cancel(),this.isStopped=!0,this.state==="idle")return;this.resolveFinishedPromise(),this.updateFinishedPromise();let{resolved:t}=this;if(!t)return;let{animation:e,keyframes:i,duration:s,type:r,ease:o,times:a}=t;if(e.playState==="idle"||e.playState==="finished")return;if(this.time){let{motionValue:c,onUpdate:h,onComplete:d,element:p,...m}=this.options,g=new ti({...m,keyframes:i,duration:s,type:r,ease:o,times:a,isGenerator:!0}),_=ge(this.time);c.setWithVelocity(g.sample(_-Rr).value,g.sample(_).value,Rr)}let{onStop:l}=this.options;l&&l(),this.cancel()}complete(){let{resolved:t}=this;t&&t.animation.finish()}cancel(){let{resolved:t}=this;t&&t.animation.cancel()}static supports(t){let{motionValue:e,name:i,repeatDelay:s,repeatType:r,damping:o,type:a}=t;if(!e||!e.owner||!(e.owner.current instanceof HTMLElement))return!1;let{onUpdate:l,transformTemplate:c}=e.owner.getProps();return Jh()&&i&&$h.has(i)&&!l&&!c&&!s&&r!=="mirror"&&o!==0&&a!=="inertia"}};var _p={type:"spring",stiffness:500,damping:25,restSpeed:10},vp=n=>({type:"spring",stiffness:550,damping:n===0?2*Math.sqrt(550):30,restSpeed:10}),yp={type:"keyframes",duration:.8},Mp={type:"keyframes",ease:[.25,.1,.35,1],duration:.3},jh=(n,{keyframes:t})=>t.length>2?yp:ze.has(n)?n.startsWith("scale")?vp(t[1]):_p:Mp;function tu({when:n,delay:t,delayChildren:e,staggerChildren:i,staggerDirection:s,repeat:r,repeatType:o,repeatDelay:a,from:l,elapsed:c,...h}){return!!Object.keys(h).length}var Pr=(n,t,e,i={},s,r)=>o=>{let a=ar(i,n)||{},l=a.delay||i.delay||0,{elapsed:c=0}=i;c=c-ge(l);let h={keyframes:Array.isArray(e)?e:[null,e],ease:"easeOut",velocity:t.getVelocity(),...a,delay:-c,onUpdate:p=>{t.set(p),a.onUpdate&&a.onUpdate(p)},onComplete:()=>{o(),a.onComplete&&a.onComplete()},name:n,motionValue:t,element:r?void 0:s};tu(a)||(h={...h,...jh(n,h)}),h.duration&&(h.duration=ge(h.duration)),h.repeatDelay&&(h.repeatDelay=ge(h.repeatDelay)),h.from!==void 0&&(h.keyframes[0]=h.from);let d=!1;if((h.type===!1||h.duration===0&&!h.repeatDelay)&&(h.duration=0,h.delay===0&&(d=!0)),(fr.current||wi.skipAnimations)&&(d=!0,h.duration=0,h.delay=0),d&&!r&&t.get()!==void 0){let p=Nn(h.keyframes,a);if(p!==void 0)return ve.update(()=>{h.onUpdate(p),h.onComplete()}),new Si([])}return!r&&Rs.supports(h)?new Rs(h):new ti(h)};function Sp({protectedKeys:n,needsAnimating:t},e){let i=n.hasOwnProperty(e)&&t[e]!==!0;return t[e]=!1,i}function eu(n,t,{delay:e=0,transitionOverride:i,type:s}={}){var r;let{transition:o=n.getDefaultTransition(),transitionEnd:a,...l}=t;i&&(o=i);let c=[],h=s&&n.animationState&&n.animationState.getState()[s];for(let d in l){let p=n.getValue(d,(r=n.latestValues[d])!==null&&r!==void 0?r:null),m=l[d];if(m===void 0||h&&Sp(h,d))continue;let g={delay:e,...ar(o||{},d)},_=!1;if(window.MotionHandoffAnimation){let f=uh(n);if(f){let b=window.MotionHandoffAnimation(f,d,ve);b!==null&&(g.startTime=b,_=!0)}}ch(n,d),p.start(Pr(d,p,m,n.shouldReduceMotion&&lr.has(d)?{type:!1}:g,n,_));let u=p.animation;u&&c.push(u)}return a&&Promise.all(c).then(()=>{ve.update(()=>{a&&ah(n,a)})}),c}function nu(n){return n instanceof SVGElement&&n.tagName!=="svg"}var iu=()=>({min:0,max:0}),Vi=()=>({x:iu(),y:iu()});var su={animation:["animate","variants","whileHover","whileTap","exit","whileInView","whileFocus","whileDrag"],exit:["exit"],drag:["drag","dragControls"],focus:["whileFocus"],hover:["whileHover","onHoverStart","onHoverEnd"],tap:["whileTap","onTap","onTapStart","onTapCancel"],pan:["onPan","onPanStart","onPanSessionStart","onPanEnd"],inView:["whileInView","onViewportEnter","onViewportLeave"],layout:["layout","layoutId"]},Ir={};for(let n in su)Ir[n]={isEnabled:t=>su[n].some(e=>!!t[e])};var ru=typeof window!="undefined";var Ps={current:null},Lr={current:!1};function ou(){if(Lr.current=!0,!!ru)if(window.matchMedia){let n=window.matchMedia("(prefers-reduced-motion)"),t=()=>Ps.current=n.matches;n.addListener(t),t()}else Ps.current=!1}var bp=[...xa,he,He],au=n=>bp.find(Er(n));function lu(n){return n!==null&&typeof n=="object"&&typeof n.start=="function"}function cu(n){return typeof n=="string"||Array.isArray(n)}var Ep=["animate","whileInView","whileFocus","whileHover","whileTap","whileDrag","exit"],hu=["initial",...Ep];function Ea(n){return lu(n.animate)||hu.some(t=>cu(n[t]))}function uu(n){return!!(Ea(n)||n.variants)}function fu(n,t,e){for(let i in t){let s=t[i],r=e[i];if(ee(s))n.addValue(i,s);else if(ee(r))n.addValue(i,fn(s,{owner:n}));else if(r!==s)if(n.hasValue(i)){let o=n.getValue(i);o.liveStyle===!0?o.jump(s):o.hasAnimated||o.set(s)}else{let o=n.getStaticValue(i);n.addValue(i,fn(o!==void 0?o:s,{owner:n}))}}for(let i in e)t[i]===void 0&&n.removeValue(i);return t}var du=["AnimationStart","AnimationComplete","Update","BeforeLayoutMeasure","LayoutMeasure","LayoutAnimationStart","LayoutAnimationComplete"],zi=class{scrapeMotionValuesFromProps(t,e,i){return{}}constructor({parent:t,props:e,presenceContext:i,reducedMotionConfig:s,blockInitialAnimation:r,visualState:o},a={}){this.current=null,this.children=new Set,this.isVariantNode=!1,this.isControllingVariants=!1,this.shouldReduceMotion=null,this.values=new Map,this.KeyframeResolver=Un,this.features={},this.valueSubscriptions=new Map,this.prevMotionValues={},this.events={},this.propEventSubscriptions={},this.notifyUpdate=()=>this.notify("Update",this.latestValues),this.render=()=>{this.current&&(this.triggerBuild(),this.renderInstance(this.current,this.renderState,this.props.style,this.projection))},this.renderScheduledAt=0,this.scheduleRender=()=>{let m=ke.now();this.renderScheduledAt<m&&(this.renderScheduledAt=m,ve.render(this.render,!1,!0))};let{latestValues:l,renderState:c,onUpdate:h}=o;this.onUpdate=h,this.latestValues=l,this.baseTarget={...l},this.initialValues=e.initial?{...l}:{},this.renderState=c,this.parent=t,this.props=e,this.presenceContext=i,this.depth=t?t.depth+1:0,this.reducedMotionConfig=s,this.options=a,this.blockInitialAnimation=!!r,this.isControllingVariants=Ea(e),this.isVariantNode=uu(e),this.isVariantNode&&(this.variantChildren=new Set),this.manuallyAnimateOnMount=!!(t&&t.current);let{willChange:d,...p}=this.scrapeMotionValuesFromProps(e,{},this);for(let m in p){let g=p[m];l[m]!==void 0&&ee(g)&&g.set(l[m],!1)}}mount(t){this.current=t,un.set(t,this),this.projection&&!this.projection.instance&&this.projection.mount(t),this.parent&&this.isVariantNode&&!this.isControllingVariants&&(this.removeFromVariantTree=this.parent.addVariantChild(this)),this.values.forEach((e,i)=>this.bindToMotionValue(i,e)),Lr.current||ou(),this.shouldReduceMotion=this.reducedMotionConfig==="never"?!1:this.reducedMotionConfig==="always"?!0:Ps.current,this.parent&&this.parent.children.add(this),this.update(this.props,this.presenceContext)}unmount(){un.delete(this.current),this.projection&&this.projection.unmount(),vs(this.notifyUpdate),vs(this.render),this.valueSubscriptions.forEach(t=>t()),this.valueSubscriptions.clear(),this.removeFromVariantTree&&this.removeFromVariantTree(),this.parent&&this.parent.children.delete(this);for(let t in this.events)this.events[t].clear();for(let t in this.features){let e=this.features[t];e&&(e.unmount(),e.isMounted=!1)}this.current=null}bindToMotionValue(t,e){this.valueSubscriptions.has(t)&&this.valueSubscriptions.get(t)();let i=ze.has(t),s=e.on("change",a=>{this.latestValues[t]=a,this.props.onUpdate&&ve.preRender(this.notifyUpdate),i&&this.projection&&(this.projection.isTransformDirty=!0)}),r=e.on("renderRequest",this.scheduleRender),o;window.MotionCheckAppearSync&&(o=window.MotionCheckAppearSync(this,t,e)),this.valueSubscriptions.set(t,()=>{s(),r(),o&&o(),e.owner&&e.stop()})}sortNodePosition(t){return!this.current||!this.sortInstanceNodePosition||this.type!==t.type?0:this.sortInstanceNodePosition(this.current,t.current)}updateFeatures(){let t="animation";for(t in Ir){let e=Ir[t];if(!e)continue;let{isEnabled:i,Feature:s}=e;if(!this.features[t]&&s&&i(this.props)&&(this.features[t]=new s(this)),this.features[t]){let r=this.features[t];r.isMounted?r.update():(r.mount(),r.isMounted=!0)}}}triggerBuild(){this.build(this.renderState,this.latestValues,this.props)}measureViewportBox(){return this.current?this.measureInstanceViewportBox(this.current,this.props):Vi()}getStaticValue(t){return this.latestValues[t]}setStaticValue(t,e){this.latestValues[t]=e}update(t,e){(t.transformTemplate||this.props.transformTemplate)&&this.scheduleRender(),this.prevProps=this.props,this.props=t,this.prevPresenceContext=this.presenceContext,this.presenceContext=e;for(let i=0;i<du.length;i++){let s=du[i];this.propEventSubscriptions[s]&&(this.propEventSubscriptions[s](),delete this.propEventSubscriptions[s]);let r="on"+s,o=t[r];o&&(this.propEventSubscriptions[s]=this.on(s,o))}this.prevMotionValues=fu(this,this.scrapeMotionValuesFromProps(t,this.prevProps,this),this.prevMotionValues),this.handleChildMotionValue&&this.handleChildMotionValue(),this.onUpdate&&this.onUpdate(this)}getProps(){return this.props}getVariant(t){return this.props.variants?this.props.variants[t]:void 0}getDefaultTransition(){return this.props.transition}getTransformPagePoint(){return this.props.transformPagePoint}getClosestVariantNode(){return this.isVariantNode?this:this.parent?this.parent.getClosestVariantNode():void 0}addVariantChild(t){let e=this.getClosestVariantNode();if(e)return e.variantChildren&&e.variantChildren.add(t),()=>e.variantChildren.delete(t)}addValue(t,e){let i=this.values.get(t);e!==i&&(i&&this.removeValue(t),this.bindToMotionValue(t,e),this.values.set(t,e),this.latestValues[t]=e.get())}removeValue(t){this.values.delete(t);let e=this.valueSubscriptions.get(t);e&&(e(),this.valueSubscriptions.delete(t)),delete this.latestValues[t],this.removeValueFromRenderState(t,this.renderState)}hasValue(t){return this.values.has(t)}getValue(t,e){if(this.props.values&&this.props.values[t])return this.props.values[t];let i=this.values.get(t);return i===void 0&&e!==void 0&&(i=fn(e===null?void 0:e,{owner:this}),this.addValue(t,i)),i}readValue(t,e){var i;let s=this.latestValues[t]!==void 0||!this.current?this.latestValues[t]:(i=this.getBaseTargetFromProps(this.props,t))!==null&&i!==void 0?i:this.readValueFromInstance(this.current,t,this.options);return s!=null&&(typeof s=="string"&&(Sr(s)||vr(s))?s=parseFloat(s):!au(s)&&He.test(e)&&(s=Mr(t,e)),this.setBaseTarget(t,ee(s)?s.get():s)),ee(s)?s.get():s}setBaseTarget(t,e){this.baseTarget[t]=e}getBaseTarget(t){var e;let{initial:i}=this.props,s;if(typeof i=="string"||typeof i=="object"){let o=ur(this.props,i,(e=this.presenceContext)===null||e===void 0?void 0:e.custom);o&&(s=o[t])}if(i&&s!==void 0)return s;let r=this.getBaseTargetFromProps(this.props,t);return r!==void 0&&!ee(r)?r:this.initialValues[t]!==void 0&&s===void 0?void 0:this.baseTarget[t]}on(t,e){return this.events[t]||(this.events[t]=new Ai),this.events[t].add(e)}notify(t,...e){this.events[t]&&this.events[t].notify(...e)}};var ki=class extends zi{constructor(){super(...arguments),this.KeyframeResolver=Ni}sortInstanceNodePosition(t,e){return t.compareDocumentPosition(e)&2?1:-1}getBaseTargetFromProps(t,e){return t.style?t.style[e]:void 0}removeValueFromRenderState(t,{vars:e,style:i}){delete e[t],delete i[t]}handleChildMotionValue(){this.childSubscription&&(this.childSubscription(),delete this.childSubscription);let{children:t}=this.props;ee(t)&&(this.childSubscription=t.on("change",e=>{this.current&&(this.current.textContent=`${e}`)}))}};var Dr=(n,t)=>t&&typeof n=="number"?t.transform(n):n;var wp={x:"translateX",y:"translateY",z:"translateZ",transformPerspective:"perspective"},Tp=nn.length;function pu(n,t,e){let i="",s=!0;for(let r=0;r<Tp;r++){let o=nn[r],a=n[o];if(a===void 0)continue;let l=!0;if(typeof a=="number"?l=a===(o.startsWith("scale")?1:0):l=parseFloat(a)===0,!l||e){let c=Dr(a,Li[o]);if(!l){s=!1;let h=wp[o]||o;i+=`${h}(${c}) `}e&&(t[o]=c)}}return i=i.trim(),e?i=e(t,s?"":i):s&&(i="none"),i}function Ur(n,t,e){let{style:i,vars:s,transformOrigin:r}=n,o=!1,a=!1;for(let l in t){let c=t[l];if(ze.has(l)){o=!0;continue}else if(br(l)){s[l]=c;continue}else{let h=Dr(c,Li[l]);l.startsWith("origin")?(a=!0,r[l]=h):i[l]=h}}if(t.transform||(o||e?i.transform=pu(t,n.transform,e):i.transform&&(i.transform="none")),a){let{originX:l="50%",originY:c="50%",originZ:h=0}=r;i.transformOrigin=`${l} ${c} ${h}`}}var Ap={offset:"stroke-dashoffset",array:"stroke-dasharray"},Cp={offset:"strokeDashoffset",array:"strokeDasharray"};function mu(n,t,e=1,i=0,s=!0){n.pathLength=1;let r=s?Ap:Cp;n[r.offset]=St.transform(-i);let o=St.transform(t),a=St.transform(e);n[r.array]=`${o} ${a}`}function gu(n,t,e){return typeof n=="string"?n:St.transform(t+e*n)}function xu(n,t,e){let i=gu(t,n.x,n.width),s=gu(e,n.y,n.height);return`${i} ${s}`}function _u(n,{attrX:t,attrY:e,attrScale:i,originX:s,originY:r,pathLength:o,pathSpacing:a=1,pathOffset:l=0,...c},h,d){if(Ur(n,c,d),h){n.style.viewBox&&(n.attrs.viewBox=n.style.viewBox);return}n.attrs=n.style,n.style={};let{attrs:p,style:m,dimensions:g}=n;p.transform&&(g&&(m.transform=p.transform),delete p.transform),g&&(s!==void 0||r!==void 0||m.transform)&&(m.transformOrigin=xu(g,s!==void 0?s:.5,r!==void 0?r:.5)),t!==void 0&&(p.x=t),e!==void 0&&(p.y=e),i!==void 0&&(p.scale=i),o!==void 0&&mu(p,o,a,l,!1)}var Nr=new Set(["baseFrequency","diffuseConstant","kernelMatrix","kernelUnitLength","keySplines","keyTimes","limitingConeAngle","markerHeight","markerWidth","numOctaves","targetX","targetY","surfaceScale","specularConstant","specularExponent","stdDeviation","tableValues","viewBox","gradientTransform","pathLength","startOffset","textLength","lengthAdjust"]);var vu=n=>typeof n=="string"&&n.toLowerCase()==="svg";function Fr(n,{style:t,vars:e},i,s){Object.assign(n.style,t,s&&s.getProjectionStyles(i));for(let r in e)n.style.setProperty(r,e[r])}function yu(n,t,e,i){Fr(n,t,void 0,i);for(let s in t.attrs)n.setAttribute(Nr.has(s)?s:Ci(s),t.attrs[s])}var Mu={};function Su(n,{layout:t,layoutId:e}){return ze.has(n)||n.startsWith("origin")||(t||e!==void 0)&&(!!Mu[n]||n==="opacity")}function Or(n,t,e){var i;let{style:s}=n,r={};for(let o in s)(ee(s[o])||t.style&&ee(t.style[o])||Su(o,n)||((i=e==null?void 0:e.getValue(o))===null||i===void 0?void 0:i.liveStyle)!==void 0)&&(r[o]=s[o]);return r}function bu(n,t,e){let i=Or(n,t,e);for(let s in n)if(ee(n[s])||ee(t[s])){let r=nn.indexOf(s)!==-1?"attr"+s.charAt(0).toUpperCase()+s.substring(1):s;i[r]=n[s]}return i}var Br=class extends ki{constructor(){super(...arguments),this.type="svg",this.isSVGTag=!1,this.measureInstanceViewportBox=Vi}getBaseTargetFromProps(t,e){return t[e]}readValueFromInstance(t,e){if(ze.has(e)){let i=Di(e);return i&&i.default||0}return e=Nr.has(e)?e:Ci(e),t.getAttribute(e)}scrapeMotionValuesFromProps(t,e,i){return bu(t,e,i)}build(t,e,i){_u(t,e,this.isSVGTag,i.transformTemplate)}renderInstance(t,e,i,s){yu(t,e,i,s)}mount(t){this.isSVGTag=vu(t.tagName),super.mount(t)}};function Eu({top:n,left:t,right:e,bottom:i}){return{x:{min:t,max:e},y:{min:n,max:i}}}function wu(n,t){if(!t)return n;let e=t({x:n.left,y:n.top}),i=t({x:n.right,y:n.bottom});return{top:e.y,left:e.x,bottom:i.y,right:i.x}}function Tu(n,t){return Eu(wu(n.getBoundingClientRect(),t))}function Rp(n){return window.getComputedStyle(n)}var Vr=class extends ki{constructor(){super(...arguments),this.type="html",this.renderInstance=Fr}readValueFromInstance(t,e){if(ze.has(e)){let i=Di(e);return i&&i.default||0}else{let i=Rp(t),s=(br(e)?i.getPropertyValue(e):i[e])||0;return typeof s=="string"?s.trim():s}}measureInstanceViewportBox(t,{transformPagePoint:e}){return Tu(t,e)}build(t,e,i){Ur(t,e,i.transformTemplate)}scrapeMotionValuesFromProps(t,e,i){return Or(t,e,i)}};function Pp(n,t){return n in t}var zr=class extends zi{constructor(){super(...arguments),this.type="object"}readValueFromInstance(t,e){if(Pp(e,t)){let i=t[e];if(typeof i=="string"||typeof i=="number")return i}}getBaseTargetFromProps(){}removeValueFromRenderState(t,e){delete e.output[t]}measureInstanceViewportBox(){return Vi()}build(t,e){Object.assign(t.output,e)}renderInstance(t,{output:e}){Object.assign(t,e)}sortInstanceNodePosition(){return 0}};function Au(n){let t={presenceContext:null,props:{},visualState:{renderState:{transform:{},transformOrigin:{},style:{},vars:{},attrs:{}},latestValues:{}}},e=nu(n)?new Br(t):new Vr(t);e.mount(n),un.set(n,e)}function Cu(n){let t={presenceContext:null,props:{},visualState:{renderState:{output:{}},latestValues:{}}},e=new zr(t);e.mount(n),un.set(n,e)}function Ru(n,t,e){let i=ee(n)?n:fn(n);return i.start(Pr("",i,t,e)),i.animation}function Ip(n,t){return ee(n)||typeof n=="number"||typeof n=="string"&&!_s(t)}function kr(n,t,e,i){let s=[];if(Ip(n,t))s.push(Ru(n,_s(t)&&t.default||t,e&&(e.default||e)));else{let r=rr(n,t,i),o=r.length;Ye(!!o,"No valid elements provided.");for(let a=0;a<o;a++){let l=r[a],c=l instanceof Element?Au:Cu;un.has(l)||c(l);let h=un.get(l),d={...e};"delay"in d&&typeof d.delay=="function"&&(d.delay=d.delay(a,o)),s.push(...eu(h,{...t,transition:d},{}))}}return s}function Pu(n,t,e){let i=[];return Qc(n,t,e,{spring:Ei}).forEach(({keyframes:r,transition:o},a)=>{i.push(...kr(a,r,o))}),i}function Lp(n){return Array.isArray(n)&&n.some(Array.isArray)}function Iu(n){function t(e,i,s){let r=[];Lp(e)?r=Pu(e,i,n):r=kr(e,i,s,n);let o=new Si(r);return n&&n.animations.push(o),o}return t}var Dt=Iu();var Dp={some:0,all:1};function wa(n,t,{root:e,margin:i,amount:s="some"}={}){let r=sr(n),o=new WeakMap,a=c=>{c.forEach(h=>{let d=o.get(h.target);if(h.isIntersecting!==!!d)if(h.isIntersecting){let p=t(h);typeof p=="function"?o.set(h.target,p):l.unobserve(h.target)}else typeof d=="function"&&(d(h),o.delete(h.target))})},l=new IntersectionObserver(a,{root:e,rootMargin:i,threshold:typeof s=="number"?s:Dp[s]});return r.forEach(c=>l.observe(c)),()=>l.disconnect()}function Up(n,t){if(n==="first")return 0;{let e=t-1;return n==="last"?e:e/2}}function ei(n=.1,{startDelay:t=0,from:e=0,ease:i}={}){return(s,r)=>{let o=typeof e=="number"?e:Up(e,r),a=Math.abs(o-s),l=n*a;if(i){let c=r*n;l=As(i)(l/c)*c}return t+l}}var yc="169";var Np=0,Lu=1,Fp=2;var Nf=1,Op=2,vn=3,Xn=0,Re=1,Je=2,Gn=0,ss=1,Mo=2,Du=3,Uu=4,Bp=5,ci=100,Vp=101,zp=102,kp=103,Hp=104,Gp=200,Wp=201,Xp=202,qp=203,sl=204,rl=205,Yp=206,Kp=207,$p=208,Zp=209,Jp=210,Qp=211,jp=212,tm=213,em=214,ol=0,al=1,ll=2,ls=3,cl=4,hl=5,ul=6,fl=7,Ff=0,nm=1,im=2,Wn=0,sm=1,rm=2,om=3,am=4,lm=5,cm=6,hm=7;var Of=300,cs=301,hs=302,dl=303,pl=304,Yo=306,ml=1e3,fi=1001,gl=1002,We=1003,um=1004;var Hr=1005;var Qe=1006,Ta=1007;var di=1008;var bn=1009,Bf=1010,Vf=1011,zs=1012,Mc=1013,pi=1014,Mn=1015,Xs=1016,Sc=1017,bc=1018,us=1020,zf=35902,kf=1021,Hf=1022,je=1023,Gf=1024,Wf=1025,rs=1026,fs=1027,Xf=1028,Ec=1029,qf=1030,wc=1031;var Tc=1033,mo=33776,go=33777,xo=33778,_o=33779,xl=35840,_l=35841,vl=35842,yl=35843,Ml=36196,Sl=37492,bl=37496,El=37808,wl=37809,Tl=37810,Al=37811,Cl=37812,Rl=37813,Pl=37814,Il=37815,Ll=37816,Dl=37817,Ul=37818,Nl=37819,Fl=37820,Ol=37821,vo=36492,Bl=36494,Vl=36495,Yf=36283,zl=36284,kl=36285,Hl=36286;var So=2300,Gl=2301,Aa=2302,Nu=2400,Fu=2401,Ou=2402;var fm=3200,dm=3201;var pm=0,mm=1,Hn="",an="srgb",$n="srgb-linear",Ac="display-p3",Ko="display-p3-linear",bo="linear",te="srgb",Eo="rec709",wo="p3";var Hi=7680;var Bu=519,gm=512,xm=513,_m=514,Kf=515,vm=516,ym=517,Mm=518,Sm=519,Vu=35044;var zu="300 es",Sn=2e3,To=2001,qn=class{addEventListener(t,e){this._listeners===void 0&&(this._listeners={});let i=this._listeners;i[t]===void 0&&(i[t]=[]),i[t].indexOf(e)===-1&&i[t].push(e)}hasEventListener(t,e){if(this._listeners===void 0)return!1;let i=this._listeners;return i[t]!==void 0&&i[t].indexOf(e)!==-1}removeEventListener(t,e){if(this._listeners===void 0)return;let s=this._listeners[t];if(s!==void 0){let r=s.indexOf(e);r!==-1&&s.splice(r,1)}}dispatchEvent(t){if(this._listeners===void 0)return;let i=this._listeners[t.type];if(i!==void 0){t.target=this;let s=i.slice(0);for(let r=0,o=s.length;r<o;r++)s[r].call(this,t);t.target=null}}},xe=["00","01","02","03","04","05","06","07","08","09","0a","0b","0c","0d","0e","0f","10","11","12","13","14","15","16","17","18","19","1a","1b","1c","1d","1e","1f","20","21","22","23","24","25","26","27","28","29","2a","2b","2c","2d","2e","2f","30","31","32","33","34","35","36","37","38","39","3a","3b","3c","3d","3e","3f","40","41","42","43","44","45","46","47","48","49","4a","4b","4c","4d","4e","4f","50","51","52","53","54","55","56","57","58","59","5a","5b","5c","5d","5e","5f","60","61","62","63","64","65","66","67","68","69","6a","6b","6c","6d","6e","6f","70","71","72","73","74","75","76","77","78","79","7a","7b","7c","7d","7e","7f","80","81","82","83","84","85","86","87","88","89","8a","8b","8c","8d","8e","8f","90","91","92","93","94","95","96","97","98","99","9a","9b","9c","9d","9e","9f","a0","a1","a2","a3","a4","a5","a6","a7","a8","a9","aa","ab","ac","ad","ae","af","b0","b1","b2","b3","b4","b5","b6","b7","b8","b9","ba","bb","bc","bd","be","bf","c0","c1","c2","c3","c4","c5","c6","c7","c8","c9","ca","cb","cc","cd","ce","cf","d0","d1","d2","d3","d4","d5","d6","d7","d8","d9","da","db","dc","dd","de","df","e0","e1","e2","e3","e4","e5","e6","e7","e8","e9","ea","eb","ec","ed","ee","ef","f0","f1","f2","f3","f4","f5","f6","f7","f8","f9","fa","fb","fc","fd","fe","ff"];var Ca=Math.PI/180,Wl=180/Math.PI;function qs(){let n=Math.random()*4294967295|0,t=Math.random()*4294967295|0,e=Math.random()*4294967295|0,i=Math.random()*4294967295|0;return(xe[n&255]+xe[n>>8&255]+xe[n>>16&255]+xe[n>>24&255]+"-"+xe[t&255]+xe[t>>8&255]+"-"+xe[t>>16&15|64]+xe[t>>24&255]+"-"+xe[e&63|128]+xe[e>>8&255]+"-"+xe[e>>16&255]+xe[e>>24&255]+xe[i&255]+xe[i>>8&255]+xe[i>>16&255]+xe[i>>24&255]).toLowerCase()}function Ce(n,t,e){return Math.max(t,Math.min(e,n))}function bm(n,t){return(n%t+t)%t}function Ra(n,t,e){return(1-e)*n+e*t}function Is(n,t){switch(t.constructor){case Float32Array:return n;case Uint32Array:return n/4294967295;case Uint16Array:return n/65535;case Uint8Array:return n/255;case Int32Array:return Math.max(n/2147483647,-1);case Int16Array:return Math.max(n/32767,-1);case Int8Array:return Math.max(n/127,-1);default:throw new Error("Invalid component type.")}}function Ae(n,t){switch(t.constructor){case Float32Array:return n;case Uint32Array:return Math.round(n*4294967295);case Uint16Array:return Math.round(n*65535);case Uint8Array:return Math.round(n*255);case Int32Array:return Math.round(n*2147483647);case Int16Array:return Math.round(n*32767);case Int8Array:return Math.round(n*127);default:throw new Error("Invalid component type.")}}var Zt=class n{constructor(t=0,e=0){n.prototype.isVector2=!0,this.x=t,this.y=e}get width(){return this.x}set width(t){this.x=t}get height(){return this.y}set height(t){this.y=t}set(t,e){return this.x=t,this.y=e,this}setScalar(t){return this.x=t,this.y=t,this}setX(t){return this.x=t,this}setY(t){return this.y=t,this}setComponent(t,e){switch(t){case 0:this.x=e;break;case 1:this.y=e;break;default:throw new Error("index is out of range: "+t)}return this}getComponent(t){switch(t){case 0:return this.x;case 1:return this.y;default:throw new Error("index is out of range: "+t)}}clone(){return new this.constructor(this.x,this.y)}copy(t){return this.x=t.x,this.y=t.y,this}add(t){return this.x+=t.x,this.y+=t.y,this}addScalar(t){return this.x+=t,this.y+=t,this}addVectors(t,e){return this.x=t.x+e.x,this.y=t.y+e.y,this}addScaledVector(t,e){return this.x+=t.x*e,this.y+=t.y*e,this}sub(t){return this.x-=t.x,this.y-=t.y,this}subScalar(t){return this.x-=t,this.y-=t,this}subVectors(t,e){return this.x=t.x-e.x,this.y=t.y-e.y,this}multiply(t){return this.x*=t.x,this.y*=t.y,this}multiplyScalar(t){return this.x*=t,this.y*=t,this}divide(t){return this.x/=t.x,this.y/=t.y,this}divideScalar(t){return this.multiplyScalar(1/t)}applyMatrix3(t){let e=this.x,i=this.y,s=t.elements;return this.x=s[0]*e+s[3]*i+s[6],this.y=s[1]*e+s[4]*i+s[7],this}min(t){return this.x=Math.min(this.x,t.x),this.y=Math.min(this.y,t.y),this}max(t){return this.x=Math.max(this.x,t.x),this.y=Math.max(this.y,t.y),this}clamp(t,e){return this.x=Math.max(t.x,Math.min(e.x,this.x)),this.y=Math.max(t.y,Math.min(e.y,this.y)),this}clampScalar(t,e){return this.x=Math.max(t,Math.min(e,this.x)),this.y=Math.max(t,Math.min(e,this.y)),this}clampLength(t,e){let i=this.length();return this.divideScalar(i||1).multiplyScalar(Math.max(t,Math.min(e,i)))}floor(){return this.x=Math.floor(this.x),this.y=Math.floor(this.y),this}ceil(){return this.x=Math.ceil(this.x),this.y=Math.ceil(this.y),this}round(){return this.x=Math.round(this.x),this.y=Math.round(this.y),this}roundToZero(){return this.x=Math.trunc(this.x),this.y=Math.trunc(this.y),this}negate(){return this.x=-this.x,this.y=-this.y,this}dot(t){return this.x*t.x+this.y*t.y}cross(t){return this.x*t.y-this.y*t.x}lengthSq(){return this.x*this.x+this.y*this.y}length(){return Math.sqrt(this.x*this.x+this.y*this.y)}manhattanLength(){return Math.abs(this.x)+Math.abs(this.y)}normalize(){return this.divideScalar(this.length()||1)}angle(){return Math.atan2(-this.y,-this.x)+Math.PI}angleTo(t){let e=Math.sqrt(this.lengthSq()*t.lengthSq());if(e===0)return Math.PI/2;let i=this.dot(t)/e;return Math.acos(Ce(i,-1,1))}distanceTo(t){return Math.sqrt(this.distanceToSquared(t))}distanceToSquared(t){let e=this.x-t.x,i=this.y-t.y;return e*e+i*i}manhattanDistanceTo(t){return Math.abs(this.x-t.x)+Math.abs(this.y-t.y)}setLength(t){return this.normalize().multiplyScalar(t)}lerp(t,e){return this.x+=(t.x-this.x)*e,this.y+=(t.y-this.y)*e,this}lerpVectors(t,e,i){return this.x=t.x+(e.x-t.x)*i,this.y=t.y+(e.y-t.y)*i,this}equals(t){return t.x===this.x&&t.y===this.y}fromArray(t,e=0){return this.x=t[e],this.y=t[e+1],this}toArray(t=[],e=0){return t[e]=this.x,t[e+1]=this.y,t}fromBufferAttribute(t,e){return this.x=t.getX(e),this.y=t.getY(e),this}rotateAround(t,e){let i=Math.cos(e),s=Math.sin(e),r=this.x-t.x,o=this.y-t.y;return this.x=r*i-o*s+t.x,this.y=r*s+o*i+t.y,this}random(){return this.x=Math.random(),this.y=Math.random(),this}*[Symbol.iterator](){yield this.x,yield this.y}},Ft=class n{constructor(t,e,i,s,r,o,a,l,c){n.prototype.isMatrix3=!0,this.elements=[1,0,0,0,1,0,0,0,1],t!==void 0&&this.set(t,e,i,s,r,o,a,l,c)}set(t,e,i,s,r,o,a,l,c){let h=this.elements;return h[0]=t,h[1]=s,h[2]=a,h[3]=e,h[4]=r,h[5]=l,h[6]=i,h[7]=o,h[8]=c,this}identity(){return this.set(1,0,0,0,1,0,0,0,1),this}copy(t){let e=this.elements,i=t.elements;return e[0]=i[0],e[1]=i[1],e[2]=i[2],e[3]=i[3],e[4]=i[4],e[5]=i[5],e[6]=i[6],e[7]=i[7],e[8]=i[8],this}extractBasis(t,e,i){return t.setFromMatrix3Column(this,0),e.setFromMatrix3Column(this,1),i.setFromMatrix3Column(this,2),this}setFromMatrix4(t){let e=t.elements;return this.set(e[0],e[4],e[8],e[1],e[5],e[9],e[2],e[6],e[10]),this}multiply(t){return this.multiplyMatrices(this,t)}premultiply(t){return this.multiplyMatrices(t,this)}multiplyMatrices(t,e){let i=t.elements,s=e.elements,r=this.elements,o=i[0],a=i[3],l=i[6],c=i[1],h=i[4],d=i[7],p=i[2],m=i[5],g=i[8],_=s[0],u=s[3],f=s[6],b=s[1],S=s[4],E=s[7],C=s[2],A=s[5],T=s[8];return r[0]=o*_+a*b+l*C,r[3]=o*u+a*S+l*A,r[6]=o*f+a*E+l*T,r[1]=c*_+h*b+d*C,r[4]=c*u+h*S+d*A,r[7]=c*f+h*E+d*T,r[2]=p*_+m*b+g*C,r[5]=p*u+m*S+g*A,r[8]=p*f+m*E+g*T,this}multiplyScalar(t){let e=this.elements;return e[0]*=t,e[3]*=t,e[6]*=t,e[1]*=t,e[4]*=t,e[7]*=t,e[2]*=t,e[5]*=t,e[8]*=t,this}determinant(){let t=this.elements,e=t[0],i=t[1],s=t[2],r=t[3],o=t[4],a=t[5],l=t[6],c=t[7],h=t[8];return e*o*h-e*a*c-i*r*h+i*a*l+s*r*c-s*o*l}invert(){let t=this.elements,e=t[0],i=t[1],s=t[2],r=t[3],o=t[4],a=t[5],l=t[6],c=t[7],h=t[8],d=h*o-a*c,p=a*l-h*r,m=c*r-o*l,g=e*d+i*p+s*m;if(g===0)return this.set(0,0,0,0,0,0,0,0,0);let _=1/g;return t[0]=d*_,t[1]=(s*c-h*i)*_,t[2]=(a*i-s*o)*_,t[3]=p*_,t[4]=(h*e-s*l)*_,t[5]=(s*r-a*e)*_,t[6]=m*_,t[7]=(i*l-c*e)*_,t[8]=(o*e-i*r)*_,this}transpose(){let t,e=this.elements;return t=e[1],e[1]=e[3],e[3]=t,t=e[2],e[2]=e[6],e[6]=t,t=e[5],e[5]=e[7],e[7]=t,this}getNormalMatrix(t){return this.setFromMatrix4(t).invert().transpose()}transposeIntoArray(t){let e=this.elements;return t[0]=e[0],t[1]=e[3],t[2]=e[6],t[3]=e[1],t[4]=e[4],t[5]=e[7],t[6]=e[2],t[7]=e[5],t[8]=e[8],this}setUvTransform(t,e,i,s,r,o,a){let l=Math.cos(r),c=Math.sin(r);return this.set(i*l,i*c,-i*(l*o+c*a)+o+t,-s*c,s*l,-s*(-c*o+l*a)+a+e,0,0,1),this}scale(t,e){return this.premultiply(Pa.makeScale(t,e)),this}rotate(t){return this.premultiply(Pa.makeRotation(-t)),this}translate(t,e){return this.premultiply(Pa.makeTranslation(t,e)),this}makeTranslation(t,e){return t.isVector2?this.set(1,0,t.x,0,1,t.y,0,0,1):this.set(1,0,t,0,1,e,0,0,1),this}makeRotation(t){let e=Math.cos(t),i=Math.sin(t);return this.set(e,-i,0,i,e,0,0,0,1),this}makeScale(t,e){return this.set(t,0,0,0,e,0,0,0,1),this}equals(t){let e=this.elements,i=t.elements;for(let s=0;s<9;s++)if(e[s]!==i[s])return!1;return!0}fromArray(t,e=0){for(let i=0;i<9;i++)this.elements[i]=t[i+e];return this}toArray(t=[],e=0){let i=this.elements;return t[e]=i[0],t[e+1]=i[1],t[e+2]=i[2],t[e+3]=i[3],t[e+4]=i[4],t[e+5]=i[5],t[e+6]=i[6],t[e+7]=i[7],t[e+8]=i[8],t}clone(){return new this.constructor().fromArray(this.elements)}},Pa=new Ft;function $f(n){for(let t=n.length-1;t>=0;--t)if(n[t]>=65535)return!0;return!1}function Ao(n){return document.createElementNS("http://www.w3.org/1999/xhtml",n)}function Em(){let n=Ao("canvas");return n.style.display="block",n}var ku={};function yo(n){n in ku||(ku[n]=!0,console.warn(n))}function wm(n,t,e){return new Promise(function(i,s){function r(){switch(n.clientWaitSync(t,n.SYNC_FLUSH_COMMANDS_BIT,0)){case n.WAIT_FAILED:s();break;case n.TIMEOUT_EXPIRED:setTimeout(r,e);break;default:i()}}setTimeout(r,e)})}function Tm(n){let t=n.elements;t[2]=.5*t[2]+.5*t[3],t[6]=.5*t[6]+.5*t[7],t[10]=.5*t[10]+.5*t[11],t[14]=.5*t[14]+.5*t[15]}function Am(n){let t=n.elements;t[11]===-1?(t[10]=-t[10]-1,t[14]=-t[14]):(t[10]=-t[10],t[14]=-t[14]+1)}var Hu=new Ft().set(.8224621,.177538,0,.0331941,.9668058,0,.0170827,.0723974,.9105199),Gu=new Ft().set(1.2249401,-.2249404,0,-.0420569,1.0420571,0,-.0196376,-.0786361,1.0982735),Ls={[$n]:{transfer:bo,primaries:Eo,luminanceCoefficients:[.2126,.7152,.0722],toReference:n=>n,fromReference:n=>n},[an]:{transfer:te,primaries:Eo,luminanceCoefficients:[.2126,.7152,.0722],toReference:n=>n.convertSRGBToLinear(),fromReference:n=>n.convertLinearToSRGB()},[Ko]:{transfer:bo,primaries:wo,luminanceCoefficients:[.2289,.6917,.0793],toReference:n=>n.applyMatrix3(Gu),fromReference:n=>n.applyMatrix3(Hu)},[Ac]:{transfer:te,primaries:wo,luminanceCoefficients:[.2289,.6917,.0793],toReference:n=>n.convertSRGBToLinear().applyMatrix3(Gu),fromReference:n=>n.applyMatrix3(Hu).convertLinearToSRGB()}},Cm=new Set([$n,Ko]),Yt={enabled:!0,_workingColorSpace:$n,get workingColorSpace(){return this._workingColorSpace},set workingColorSpace(n){if(!Cm.has(n))throw new Error(`Unsupported working color space, "${n}".`);this._workingColorSpace=n},convert:function(n,t,e){if(this.enabled===!1||t===e||!t||!e)return n;let i=Ls[t].toReference,s=Ls[e].fromReference;return s(i(n))},fromWorkingColorSpace:function(n,t){return this.convert(n,this._workingColorSpace,t)},toWorkingColorSpace:function(n,t){return this.convert(n,t,this._workingColorSpace)},getPrimaries:function(n){return Ls[n].primaries},getTransfer:function(n){return n===Hn?bo:Ls[n].transfer},getLuminanceCoefficients:function(n,t=this._workingColorSpace){return n.fromArray(Ls[t].luminanceCoefficients)}};function os(n){return n<.04045?n*.0773993808:Math.pow(n*.9478672986+.0521327014,2.4)}function Ia(n){return n<.0031308?n*12.92:1.055*Math.pow(n,.41666)-.055}var Gi,Xl=class{static getDataURL(t){if(/^data:/i.test(t.src)||typeof HTMLCanvasElement=="undefined")return t.src;let e;if(t instanceof HTMLCanvasElement)e=t;else{Gi===void 0&&(Gi=Ao("canvas")),Gi.width=t.width,Gi.height=t.height;let i=Gi.getContext("2d");t instanceof ImageData?i.putImageData(t,0,0):i.drawImage(t,0,0,t.width,t.height),e=Gi}return e.width>2048||e.height>2048?(console.warn("THREE.ImageUtils.getDataURL: Image converted to jpg for performance reasons",t),e.toDataURL("image/jpeg",.6)):e.toDataURL("image/png")}static sRGBToLinear(t){if(typeof HTMLImageElement!="undefined"&&t instanceof HTMLImageElement||typeof HTMLCanvasElement!="undefined"&&t instanceof HTMLCanvasElement||typeof ImageBitmap!="undefined"&&t instanceof ImageBitmap){let e=Ao("canvas");e.width=t.width,e.height=t.height;let i=e.getContext("2d");i.drawImage(t,0,0,t.width,t.height);let s=i.getImageData(0,0,t.width,t.height),r=s.data;for(let o=0;o<r.length;o++)r[o]=os(r[o]/255)*255;return i.putImageData(s,0,0),e}else if(t.data){let e=t.data.slice(0);for(let i=0;i<e.length;i++)e instanceof Uint8Array||e instanceof Uint8ClampedArray?e[i]=Math.floor(os(e[i]/255)*255):e[i]=os(e[i]);return{data:e,width:t.width,height:t.height}}else return console.warn("THREE.ImageUtils.sRGBToLinear(): Unsupported image type. No color space conversion applied."),t}},Rm=0,Co=class{constructor(t=null){this.isSource=!0,Object.defineProperty(this,"id",{value:Rm++}),this.uuid=qs(),this.data=t,this.dataReady=!0,this.version=0}set needsUpdate(t){t===!0&&this.version++}toJSON(t){let e=t===void 0||typeof t=="string";if(!e&&t.images[this.uuid]!==void 0)return t.images[this.uuid];let i={uuid:this.uuid,url:""},s=this.data;if(s!==null){let r;if(Array.isArray(s)){r=[];for(let o=0,a=s.length;o<a;o++)s[o].isDataTexture?r.push(La(s[o].image)):r.push(La(s[o]))}else r=La(s);i.url=r}return e||(t.images[this.uuid]=i),i}};function La(n){return typeof HTMLImageElement!="undefined"&&n instanceof HTMLImageElement||typeof HTMLCanvasElement!="undefined"&&n instanceof HTMLCanvasElement||typeof ImageBitmap!="undefined"&&n instanceof ImageBitmap?Xl.getDataURL(n):n.data?{data:Array.from(n.data),width:n.width,height:n.height,type:n.data.constructor.name}:(console.warn("THREE.Texture: Unable to serialize Texture."),{})}var Pm=0,Xe=class n extends qn{constructor(t=n.DEFAULT_IMAGE,e=n.DEFAULT_MAPPING,i=fi,s=fi,r=Qe,o=di,a=je,l=bn,c=n.DEFAULT_ANISOTROPY,h=Hn){super(),this.isTexture=!0,Object.defineProperty(this,"id",{value:Pm++}),this.uuid=qs(),this.name="",this.source=new Co(t),this.mipmaps=[],this.mapping=e,this.channel=0,this.wrapS=i,this.wrapT=s,this.magFilter=r,this.minFilter=o,this.anisotropy=c,this.format=a,this.internalFormat=null,this.type=l,this.offset=new Zt(0,0),this.repeat=new Zt(1,1),this.center=new Zt(0,0),this.rotation=0,this.matrixAutoUpdate=!0,this.matrix=new Ft,this.generateMipmaps=!0,this.premultiplyAlpha=!1,this.flipY=!0,this.unpackAlignment=4,this.colorSpace=h,this.userData={},this.version=0,this.onUpdate=null,this.isRenderTargetTexture=!1,this.pmremVersion=0}get image(){return this.source.data}set image(t=null){this.source.data=t}updateMatrix(){this.matrix.setUvTransform(this.offset.x,this.offset.y,this.repeat.x,this.repeat.y,this.rotation,this.center.x,this.center.y)}clone(){return new this.constructor().copy(this)}copy(t){return this.name=t.name,this.source=t.source,this.mipmaps=t.mipmaps.slice(0),this.mapping=t.mapping,this.channel=t.channel,this.wrapS=t.wrapS,this.wrapT=t.wrapT,this.magFilter=t.magFilter,this.minFilter=t.minFilter,this.anisotropy=t.anisotropy,this.format=t.format,this.internalFormat=t.internalFormat,this.type=t.type,this.offset.copy(t.offset),this.repeat.copy(t.repeat),this.center.copy(t.center),this.rotation=t.rotation,this.matrixAutoUpdate=t.matrixAutoUpdate,this.matrix.copy(t.matrix),this.generateMipmaps=t.generateMipmaps,this.premultiplyAlpha=t.premultiplyAlpha,this.flipY=t.flipY,this.unpackAlignment=t.unpackAlignment,this.colorSpace=t.colorSpace,this.userData=JSON.parse(JSON.stringify(t.userData)),this.needsUpdate=!0,this}toJSON(t){let e=t===void 0||typeof t=="string";if(!e&&t.textures[this.uuid]!==void 0)return t.textures[this.uuid];let i={metadata:{version:4.6,type:"Texture",generator:"Texture.toJSON"},uuid:this.uuid,name:this.name,image:this.source.toJSON(t).uuid,mapping:this.mapping,channel:this.channel,repeat:[this.repeat.x,this.repeat.y],offset:[this.offset.x,this.offset.y],center:[this.center.x,this.center.y],rotation:this.rotation,wrap:[this.wrapS,this.wrapT],format:this.format,internalFormat:this.internalFormat,type:this.type,colorSpace:this.colorSpace,minFilter:this.minFilter,magFilter:this.magFilter,anisotropy:this.anisotropy,flipY:this.flipY,generateMipmaps:this.generateMipmaps,premultiplyAlpha:this.premultiplyAlpha,unpackAlignment:this.unpackAlignment};return Object.keys(this.userData).length>0&&(i.userData=this.userData),e||(t.textures[this.uuid]=i),i}dispose(){this.dispatchEvent({type:"dispose"})}transformUv(t){if(this.mapping!==Of)return t;if(t.applyMatrix3(this.matrix),t.x<0||t.x>1)switch(this.wrapS){case ml:t.x=t.x-Math.floor(t.x);break;case fi:t.x=t.x<0?0:1;break;case gl:Math.abs(Math.floor(t.x)%2)===1?t.x=Math.ceil(t.x)-t.x:t.x=t.x-Math.floor(t.x);break}if(t.y<0||t.y>1)switch(this.wrapT){case ml:t.y=t.y-Math.floor(t.y);break;case fi:t.y=t.y<0?0:1;break;case gl:Math.abs(Math.floor(t.y)%2)===1?t.y=Math.ceil(t.y)-t.y:t.y=t.y-Math.floor(t.y);break}return this.flipY&&(t.y=1-t.y),t}set needsUpdate(t){t===!0&&(this.version++,this.source.needsUpdate=!0)}set needsPMREMUpdate(t){t===!0&&this.pmremVersion++}};Xe.DEFAULT_IMAGE=null;Xe.DEFAULT_MAPPING=Of;Xe.DEFAULT_ANISOTROPY=1;var ae=class n{constructor(t=0,e=0,i=0,s=1){n.prototype.isVector4=!0,this.x=t,this.y=e,this.z=i,this.w=s}get width(){return this.z}set width(t){this.z=t}get height(){return this.w}set height(t){this.w=t}set(t,e,i,s){return this.x=t,this.y=e,this.z=i,this.w=s,this}setScalar(t){return this.x=t,this.y=t,this.z=t,this.w=t,this}setX(t){return this.x=t,this}setY(t){return this.y=t,this}setZ(t){return this.z=t,this}setW(t){return this.w=t,this}setComponent(t,e){switch(t){case 0:this.x=e;break;case 1:this.y=e;break;case 2:this.z=e;break;case 3:this.w=e;break;default:throw new Error("index is out of range: "+t)}return this}getComponent(t){switch(t){case 0:return this.x;case 1:return this.y;case 2:return this.z;case 3:return this.w;default:throw new Error("index is out of range: "+t)}}clone(){return new this.constructor(this.x,this.y,this.z,this.w)}copy(t){return this.x=t.x,this.y=t.y,this.z=t.z,this.w=t.w!==void 0?t.w:1,this}add(t){return this.x+=t.x,this.y+=t.y,this.z+=t.z,this.w+=t.w,this}addScalar(t){return this.x+=t,this.y+=t,this.z+=t,this.w+=t,this}addVectors(t,e){return this.x=t.x+e.x,this.y=t.y+e.y,this.z=t.z+e.z,this.w=t.w+e.w,this}addScaledVector(t,e){return this.x+=t.x*e,this.y+=t.y*e,this.z+=t.z*e,this.w+=t.w*e,this}sub(t){return this.x-=t.x,this.y-=t.y,this.z-=t.z,this.w-=t.w,this}subScalar(t){return this.x-=t,this.y-=t,this.z-=t,this.w-=t,this}subVectors(t,e){return this.x=t.x-e.x,this.y=t.y-e.y,this.z=t.z-e.z,this.w=t.w-e.w,this}multiply(t){return this.x*=t.x,this.y*=t.y,this.z*=t.z,this.w*=t.w,this}multiplyScalar(t){return this.x*=t,this.y*=t,this.z*=t,this.w*=t,this}applyMatrix4(t){let e=this.x,i=this.y,s=this.z,r=this.w,o=t.elements;return this.x=o[0]*e+o[4]*i+o[8]*s+o[12]*r,this.y=o[1]*e+o[5]*i+o[9]*s+o[13]*r,this.z=o[2]*e+o[6]*i+o[10]*s+o[14]*r,this.w=o[3]*e+o[7]*i+o[11]*s+o[15]*r,this}divideScalar(t){return this.multiplyScalar(1/t)}setAxisAngleFromQuaternion(t){this.w=2*Math.acos(t.w);let e=Math.sqrt(1-t.w*t.w);return e<1e-4?(this.x=1,this.y=0,this.z=0):(this.x=t.x/e,this.y=t.y/e,this.z=t.z/e),this}setAxisAngleFromRotationMatrix(t){let e,i,s,r,l=t.elements,c=l[0],h=l[4],d=l[8],p=l[1],m=l[5],g=l[9],_=l[2],u=l[6],f=l[10];if(Math.abs(h-p)<.01&&Math.abs(d-_)<.01&&Math.abs(g-u)<.01){if(Math.abs(h+p)<.1&&Math.abs(d+_)<.1&&Math.abs(g+u)<.1&&Math.abs(c+m+f-3)<.1)return this.set(1,0,0,0),this;e=Math.PI;let S=(c+1)/2,E=(m+1)/2,C=(f+1)/2,A=(h+p)/4,T=(d+_)/4,P=(g+u)/4;return S>E&&S>C?S<.01?(i=0,s=.707106781,r=.707106781):(i=Math.sqrt(S),s=A/i,r=T/i):E>C?E<.01?(i=.707106781,s=0,r=.707106781):(s=Math.sqrt(E),i=A/s,r=P/s):C<.01?(i=.707106781,s=.707106781,r=0):(r=Math.sqrt(C),i=T/r,s=P/r),this.set(i,s,r,e),this}let b=Math.sqrt((u-g)*(u-g)+(d-_)*(d-_)+(p-h)*(p-h));return Math.abs(b)<.001&&(b=1),this.x=(u-g)/b,this.y=(d-_)/b,this.z=(p-h)/b,this.w=Math.acos((c+m+f-1)/2),this}setFromMatrixPosition(t){let e=t.elements;return this.x=e[12],this.y=e[13],this.z=e[14],this.w=e[15],this}min(t){return this.x=Math.min(this.x,t.x),this.y=Math.min(this.y,t.y),this.z=Math.min(this.z,t.z),this.w=Math.min(this.w,t.w),this}max(t){return this.x=Math.max(this.x,t.x),this.y=Math.max(this.y,t.y),this.z=Math.max(this.z,t.z),this.w=Math.max(this.w,t.w),this}clamp(t,e){return this.x=Math.max(t.x,Math.min(e.x,this.x)),this.y=Math.max(t.y,Math.min(e.y,this.y)),this.z=Math.max(t.z,Math.min(e.z,this.z)),this.w=Math.max(t.w,Math.min(e.w,this.w)),this}clampScalar(t,e){return this.x=Math.max(t,Math.min(e,this.x)),this.y=Math.max(t,Math.min(e,this.y)),this.z=Math.max(t,Math.min(e,this.z)),this.w=Math.max(t,Math.min(e,this.w)),this}clampLength(t,e){let i=this.length();return this.divideScalar(i||1).multiplyScalar(Math.max(t,Math.min(e,i)))}floor(){return this.x=Math.floor(this.x),this.y=Math.floor(this.y),this.z=Math.floor(this.z),this.w=Math.floor(this.w),this}ceil(){return this.x=Math.ceil(this.x),this.y=Math.ceil(this.y),this.z=Math.ceil(this.z),this.w=Math.ceil(this.w),this}round(){return this.x=Math.round(this.x),this.y=Math.round(this.y),this.z=Math.round(this.z),this.w=Math.round(this.w),this}roundToZero(){return this.x=Math.trunc(this.x),this.y=Math.trunc(this.y),this.z=Math.trunc(this.z),this.w=Math.trunc(this.w),this}negate(){return this.x=-this.x,this.y=-this.y,this.z=-this.z,this.w=-this.w,this}dot(t){return this.x*t.x+this.y*t.y+this.z*t.z+this.w*t.w}lengthSq(){return this.x*this.x+this.y*this.y+this.z*this.z+this.w*this.w}length(){return Math.sqrt(this.x*this.x+this.y*this.y+this.z*this.z+this.w*this.w)}manhattanLength(){return Math.abs(this.x)+Math.abs(this.y)+Math.abs(this.z)+Math.abs(this.w)}normalize(){return this.divideScalar(this.length()||1)}setLength(t){return this.normalize().multiplyScalar(t)}lerp(t,e){return this.x+=(t.x-this.x)*e,this.y+=(t.y-this.y)*e,this.z+=(t.z-this.z)*e,this.w+=(t.w-this.w)*e,this}lerpVectors(t,e,i){return this.x=t.x+(e.x-t.x)*i,this.y=t.y+(e.y-t.y)*i,this.z=t.z+(e.z-t.z)*i,this.w=t.w+(e.w-t.w)*i,this}equals(t){return t.x===this.x&&t.y===this.y&&t.z===this.z&&t.w===this.w}fromArray(t,e=0){return this.x=t[e],this.y=t[e+1],this.z=t[e+2],this.w=t[e+3],this}toArray(t=[],e=0){return t[e]=this.x,t[e+1]=this.y,t[e+2]=this.z,t[e+3]=this.w,t}fromBufferAttribute(t,e){return this.x=t.getX(e),this.y=t.getY(e),this.z=t.getZ(e),this.w=t.getW(e),this}random(){return this.x=Math.random(),this.y=Math.random(),this.z=Math.random(),this.w=Math.random(),this}*[Symbol.iterator](){yield this.x,yield this.y,yield this.z,yield this.w}},ql=class extends qn{constructor(t=1,e=1,i={}){super(),this.isRenderTarget=!0,this.width=t,this.height=e,this.depth=1,this.scissor=new ae(0,0,t,e),this.scissorTest=!1,this.viewport=new ae(0,0,t,e);let s={width:t,height:e,depth:1};i=Object.assign({generateMipmaps:!1,internalFormat:null,minFilter:Qe,depthBuffer:!0,stencilBuffer:!1,resolveDepthBuffer:!0,resolveStencilBuffer:!0,depthTexture:null,samples:0,count:1},i);let r=new Xe(s,i.mapping,i.wrapS,i.wrapT,i.magFilter,i.minFilter,i.format,i.type,i.anisotropy,i.colorSpace);r.flipY=!1,r.generateMipmaps=i.generateMipmaps,r.internalFormat=i.internalFormat,this.textures=[];let o=i.count;for(let a=0;a<o;a++)this.textures[a]=r.clone(),this.textures[a].isRenderTargetTexture=!0;this.depthBuffer=i.depthBuffer,this.stencilBuffer=i.stencilBuffer,this.resolveDepthBuffer=i.resolveDepthBuffer,this.resolveStencilBuffer=i.resolveStencilBuffer,this.depthTexture=i.depthTexture,this.samples=i.samples}get texture(){return this.textures[0]}set texture(t){this.textures[0]=t}setSize(t,e,i=1){if(this.width!==t||this.height!==e||this.depth!==i){this.width=t,this.height=e,this.depth=i;for(let s=0,r=this.textures.length;s<r;s++)this.textures[s].image.width=t,this.textures[s].image.height=e,this.textures[s].image.depth=i;this.dispose()}this.viewport.set(0,0,t,e),this.scissor.set(0,0,t,e)}clone(){return new this.constructor().copy(this)}copy(t){this.width=t.width,this.height=t.height,this.depth=t.depth,this.scissor.copy(t.scissor),this.scissorTest=t.scissorTest,this.viewport.copy(t.viewport),this.textures.length=0;for(let i=0,s=t.textures.length;i<s;i++)this.textures[i]=t.textures[i].clone(),this.textures[i].isRenderTargetTexture=!0;let e=Object.assign({},t.texture.image);return this.texture.source=new Co(e),this.depthBuffer=t.depthBuffer,this.stencilBuffer=t.stencilBuffer,this.resolveDepthBuffer=t.resolveDepthBuffer,this.resolveStencilBuffer=t.resolveStencilBuffer,t.depthTexture!==null&&(this.depthTexture=t.depthTexture.clone()),this.samples=t.samples,this}dispose(){this.dispatchEvent({type:"dispose"})}},En=class extends ql{constructor(t=1,e=1,i={}){super(t,e,i),this.isWebGLRenderTarget=!0}},Ro=class extends Xe{constructor(t=null,e=1,i=1,s=1){super(null),this.isDataArrayTexture=!0,this.image={data:t,width:e,height:i,depth:s},this.magFilter=We,this.minFilter=We,this.wrapR=fi,this.generateMipmaps=!1,this.flipY=!1,this.unpackAlignment=1,this.layerUpdates=new Set}addLayerUpdate(t){this.layerUpdates.add(t)}clearLayerUpdates(){this.layerUpdates.clear()}};var Yl=class extends Xe{constructor(t=null,e=1,i=1,s=1){super(null),this.isData3DTexture=!0,this.image={data:t,width:e,height:i,depth:s},this.magFilter=We,this.minFilter=We,this.wrapR=fi,this.generateMipmaps=!1,this.flipY=!1,this.unpackAlignment=1}};var Yn=class{constructor(t=0,e=0,i=0,s=1){this.isQuaternion=!0,this._x=t,this._y=e,this._z=i,this._w=s}static slerpFlat(t,e,i,s,r,o,a){let l=i[s+0],c=i[s+1],h=i[s+2],d=i[s+3],p=r[o+0],m=r[o+1],g=r[o+2],_=r[o+3];if(a===0){t[e+0]=l,t[e+1]=c,t[e+2]=h,t[e+3]=d;return}if(a===1){t[e+0]=p,t[e+1]=m,t[e+2]=g,t[e+3]=_;return}if(d!==_||l!==p||c!==m||h!==g){let u=1-a,f=l*p+c*m+h*g+d*_,b=f>=0?1:-1,S=1-f*f;if(S>Number.EPSILON){let C=Math.sqrt(S),A=Math.atan2(C,f*b);u=Math.sin(u*A)/C,a=Math.sin(a*A)/C}let E=a*b;if(l=l*u+p*E,c=c*u+m*E,h=h*u+g*E,d=d*u+_*E,u===1-a){let C=1/Math.sqrt(l*l+c*c+h*h+d*d);l*=C,c*=C,h*=C,d*=C}}t[e]=l,t[e+1]=c,t[e+2]=h,t[e+3]=d}static multiplyQuaternionsFlat(t,e,i,s,r,o){let a=i[s],l=i[s+1],c=i[s+2],h=i[s+3],d=r[o],p=r[o+1],m=r[o+2],g=r[o+3];return t[e]=a*g+h*d+l*m-c*p,t[e+1]=l*g+h*p+c*d-a*m,t[e+2]=c*g+h*m+a*p-l*d,t[e+3]=h*g-a*d-l*p-c*m,t}get x(){return this._x}set x(t){this._x=t,this._onChangeCallback()}get y(){return this._y}set y(t){this._y=t,this._onChangeCallback()}get z(){return this._z}set z(t){this._z=t,this._onChangeCallback()}get w(){return this._w}set w(t){this._w=t,this._onChangeCallback()}set(t,e,i,s){return this._x=t,this._y=e,this._z=i,this._w=s,this._onChangeCallback(),this}clone(){return new this.constructor(this._x,this._y,this._z,this._w)}copy(t){return this._x=t.x,this._y=t.y,this._z=t.z,this._w=t.w,this._onChangeCallback(),this}setFromEuler(t,e=!0){let i=t._x,s=t._y,r=t._z,o=t._order,a=Math.cos,l=Math.sin,c=a(i/2),h=a(s/2),d=a(r/2),p=l(i/2),m=l(s/2),g=l(r/2);switch(o){case"XYZ":this._x=p*h*d+c*m*g,this._y=c*m*d-p*h*g,this._z=c*h*g+p*m*d,this._w=c*h*d-p*m*g;break;case"YXZ":this._x=p*h*d+c*m*g,this._y=c*m*d-p*h*g,this._z=c*h*g-p*m*d,this._w=c*h*d+p*m*g;break;case"ZXY":this._x=p*h*d-c*m*g,this._y=c*m*d+p*h*g,this._z=c*h*g+p*m*d,this._w=c*h*d-p*m*g;break;case"ZYX":this._x=p*h*d-c*m*g,this._y=c*m*d+p*h*g,this._z=c*h*g-p*m*d,this._w=c*h*d+p*m*g;break;case"YZX":this._x=p*h*d+c*m*g,this._y=c*m*d+p*h*g,this._z=c*h*g-p*m*d,this._w=c*h*d-p*m*g;break;case"XZY":this._x=p*h*d-c*m*g,this._y=c*m*d-p*h*g,this._z=c*h*g+p*m*d,this._w=c*h*d+p*m*g;break;default:console.warn("THREE.Quaternion: .setFromEuler() encountered an unknown order: "+o)}return e===!0&&this._onChangeCallback(),this}setFromAxisAngle(t,e){let i=e/2,s=Math.sin(i);return this._x=t.x*s,this._y=t.y*s,this._z=t.z*s,this._w=Math.cos(i),this._onChangeCallback(),this}setFromRotationMatrix(t){let e=t.elements,i=e[0],s=e[4],r=e[8],o=e[1],a=e[5],l=e[9],c=e[2],h=e[6],d=e[10],p=i+a+d;if(p>0){let m=.5/Math.sqrt(p+1);this._w=.25/m,this._x=(h-l)*m,this._y=(r-c)*m,this._z=(o-s)*m}else if(i>a&&i>d){let m=2*Math.sqrt(1+i-a-d);this._w=(h-l)/m,this._x=.25*m,this._y=(s+o)/m,this._z=(r+c)/m}else if(a>d){let m=2*Math.sqrt(1+a-i-d);this._w=(r-c)/m,this._x=(s+o)/m,this._y=.25*m,this._z=(l+h)/m}else{let m=2*Math.sqrt(1+d-i-a);this._w=(o-s)/m,this._x=(r+c)/m,this._y=(l+h)/m,this._z=.25*m}return this._onChangeCallback(),this}setFromUnitVectors(t,e){let i=t.dot(e)+1;return i<Number.EPSILON?(i=0,Math.abs(t.x)>Math.abs(t.z)?(this._x=-t.y,this._y=t.x,this._z=0,this._w=i):(this._x=0,this._y=-t.z,this._z=t.y,this._w=i)):(this._x=t.y*e.z-t.z*e.y,this._y=t.z*e.x-t.x*e.z,this._z=t.x*e.y-t.y*e.x,this._w=i),this.normalize()}angleTo(t){return 2*Math.acos(Math.abs(Ce(this.dot(t),-1,1)))}rotateTowards(t,e){let i=this.angleTo(t);if(i===0)return this;let s=Math.min(1,e/i);return this.slerp(t,s),this}identity(){return this.set(0,0,0,1)}invert(){return this.conjugate()}conjugate(){return this._x*=-1,this._y*=-1,this._z*=-1,this._onChangeCallback(),this}dot(t){return this._x*t._x+this._y*t._y+this._z*t._z+this._w*t._w}lengthSq(){return this._x*this._x+this._y*this._y+this._z*this._z+this._w*this._w}length(){return Math.sqrt(this._x*this._x+this._y*this._y+this._z*this._z+this._w*this._w)}normalize(){let t=this.length();return t===0?(this._x=0,this._y=0,this._z=0,this._w=1):(t=1/t,this._x=this._x*t,this._y=this._y*t,this._z=this._z*t,this._w=this._w*t),this._onChangeCallback(),this}multiply(t){return this.multiplyQuaternions(this,t)}premultiply(t){return this.multiplyQuaternions(t,this)}multiplyQuaternions(t,e){let i=t._x,s=t._y,r=t._z,o=t._w,a=e._x,l=e._y,c=e._z,h=e._w;return this._x=i*h+o*a+s*c-r*l,this._y=s*h+o*l+r*a-i*c,this._z=r*h+o*c+i*l-s*a,this._w=o*h-i*a-s*l-r*c,this._onChangeCallback(),this}slerp(t,e){if(e===0)return this;if(e===1)return this.copy(t);let i=this._x,s=this._y,r=this._z,o=this._w,a=o*t._w+i*t._x+s*t._y+r*t._z;if(a<0?(this._w=-t._w,this._x=-t._x,this._y=-t._y,this._z=-t._z,a=-a):this.copy(t),a>=1)return this._w=o,this._x=i,this._y=s,this._z=r,this;let l=1-a*a;if(l<=Number.EPSILON){let m=1-e;return this._w=m*o+e*this._w,this._x=m*i+e*this._x,this._y=m*s+e*this._y,this._z=m*r+e*this._z,this.normalize(),this}let c=Math.sqrt(l),h=Math.atan2(c,a),d=Math.sin((1-e)*h)/c,p=Math.sin(e*h)/c;return this._w=o*d+this._w*p,this._x=i*d+this._x*p,this._y=s*d+this._y*p,this._z=r*d+this._z*p,this._onChangeCallback(),this}slerpQuaternions(t,e,i){return this.copy(t).slerp(e,i)}random(){let t=2*Math.PI*Math.random(),e=2*Math.PI*Math.random(),i=Math.random(),s=Math.sqrt(1-i),r=Math.sqrt(i);return this.set(s*Math.sin(t),s*Math.cos(t),r*Math.sin(e),r*Math.cos(e))}equals(t){return t._x===this._x&&t._y===this._y&&t._z===this._z&&t._w===this._w}fromArray(t,e=0){return this._x=t[e],this._y=t[e+1],this._z=t[e+2],this._w=t[e+3],this._onChangeCallback(),this}toArray(t=[],e=0){return t[e]=this._x,t[e+1]=this._y,t[e+2]=this._z,t[e+3]=this._w,t}fromBufferAttribute(t,e){return this._x=t.getX(e),this._y=t.getY(e),this._z=t.getZ(e),this._w=t.getW(e),this._onChangeCallback(),this}toJSON(){return this.toArray()}_onChange(t){return this._onChangeCallback=t,this}_onChangeCallback(){}*[Symbol.iterator](){yield this._x,yield this._y,yield this._z,yield this._w}},F=class n{constructor(t=0,e=0,i=0){n.prototype.isVector3=!0,this.x=t,this.y=e,this.z=i}set(t,e,i){return i===void 0&&(i=this.z),this.x=t,this.y=e,this.z=i,this}setScalar(t){return this.x=t,this.y=t,this.z=t,this}setX(t){return this.x=t,this}setY(t){return this.y=t,this}setZ(t){return this.z=t,this}setComponent(t,e){switch(t){case 0:this.x=e;break;case 1:this.y=e;break;case 2:this.z=e;break;default:throw new Error("index is out of range: "+t)}return this}getComponent(t){switch(t){case 0:return this.x;case 1:return this.y;case 2:return this.z;default:throw new Error("index is out of range: "+t)}}clone(){return new this.constructor(this.x,this.y,this.z)}copy(t){return this.x=t.x,this.y=t.y,this.z=t.z,this}add(t){return this.x+=t.x,this.y+=t.y,this.z+=t.z,this}addScalar(t){return this.x+=t,this.y+=t,this.z+=t,this}addVectors(t,e){return this.x=t.x+e.x,this.y=t.y+e.y,this.z=t.z+e.z,this}addScaledVector(t,e){return this.x+=t.x*e,this.y+=t.y*e,this.z+=t.z*e,this}sub(t){return this.x-=t.x,this.y-=t.y,this.z-=t.z,this}subScalar(t){return this.x-=t,this.y-=t,this.z-=t,this}subVectors(t,e){return this.x=t.x-e.x,this.y=t.y-e.y,this.z=t.z-e.z,this}multiply(t){return this.x*=t.x,this.y*=t.y,this.z*=t.z,this}multiplyScalar(t){return this.x*=t,this.y*=t,this.z*=t,this}multiplyVectors(t,e){return this.x=t.x*e.x,this.y=t.y*e.y,this.z=t.z*e.z,this}applyEuler(t){return this.applyQuaternion(Wu.setFromEuler(t))}applyAxisAngle(t,e){return this.applyQuaternion(Wu.setFromAxisAngle(t,e))}applyMatrix3(t){let e=this.x,i=this.y,s=this.z,r=t.elements;return this.x=r[0]*e+r[3]*i+r[6]*s,this.y=r[1]*e+r[4]*i+r[7]*s,this.z=r[2]*e+r[5]*i+r[8]*s,this}applyNormalMatrix(t){return this.applyMatrix3(t).normalize()}applyMatrix4(t){let e=this.x,i=this.y,s=this.z,r=t.elements,o=1/(r[3]*e+r[7]*i+r[11]*s+r[15]);return this.x=(r[0]*e+r[4]*i+r[8]*s+r[12])*o,this.y=(r[1]*e+r[5]*i+r[9]*s+r[13])*o,this.z=(r[2]*e+r[6]*i+r[10]*s+r[14])*o,this}applyQuaternion(t){let e=this.x,i=this.y,s=this.z,r=t.x,o=t.y,a=t.z,l=t.w,c=2*(o*s-a*i),h=2*(a*e-r*s),d=2*(r*i-o*e);return this.x=e+l*c+o*d-a*h,this.y=i+l*h+a*c-r*d,this.z=s+l*d+r*h-o*c,this}project(t){return this.applyMatrix4(t.matrixWorldInverse).applyMatrix4(t.projectionMatrix)}unproject(t){return this.applyMatrix4(t.projectionMatrixInverse).applyMatrix4(t.matrixWorld)}transformDirection(t){let e=this.x,i=this.y,s=this.z,r=t.elements;return this.x=r[0]*e+r[4]*i+r[8]*s,this.y=r[1]*e+r[5]*i+r[9]*s,this.z=r[2]*e+r[6]*i+r[10]*s,this.normalize()}divide(t){return this.x/=t.x,this.y/=t.y,this.z/=t.z,this}divideScalar(t){return this.multiplyScalar(1/t)}min(t){return this.x=Math.min(this.x,t.x),this.y=Math.min(this.y,t.y),this.z=Math.min(this.z,t.z),this}max(t){return this.x=Math.max(this.x,t.x),this.y=Math.max(this.y,t.y),this.z=Math.max(this.z,t.z),this}clamp(t,e){return this.x=Math.max(t.x,Math.min(e.x,this.x)),this.y=Math.max(t.y,Math.min(e.y,this.y)),this.z=Math.max(t.z,Math.min(e.z,this.z)),this}clampScalar(t,e){return this.x=Math.max(t,Math.min(e,this.x)),this.y=Math.max(t,Math.min(e,this.y)),this.z=Math.max(t,Math.min(e,this.z)),this}clampLength(t,e){let i=this.length();return this.divideScalar(i||1).multiplyScalar(Math.max(t,Math.min(e,i)))}floor(){return this.x=Math.floor(this.x),this.y=Math.floor(this.y),this.z=Math.floor(this.z),this}ceil(){return this.x=Math.ceil(this.x),this.y=Math.ceil(this.y),this.z=Math.ceil(this.z),this}round(){return this.x=Math.round(this.x),this.y=Math.round(this.y),this.z=Math.round(this.z),this}roundToZero(){return this.x=Math.trunc(this.x),this.y=Math.trunc(this.y),this.z=Math.trunc(this.z),this}negate(){return this.x=-this.x,this.y=-this.y,this.z=-this.z,this}dot(t){return this.x*t.x+this.y*t.y+this.z*t.z}lengthSq(){return this.x*this.x+this.y*this.y+this.z*this.z}length(){return Math.sqrt(this.x*this.x+this.y*this.y+this.z*this.z)}manhattanLength(){return Math.abs(this.x)+Math.abs(this.y)+Math.abs(this.z)}normalize(){return this.divideScalar(this.length()||1)}setLength(t){return this.normalize().multiplyScalar(t)}lerp(t,e){return this.x+=(t.x-this.x)*e,this.y+=(t.y-this.y)*e,this.z+=(t.z-this.z)*e,this}lerpVectors(t,e,i){return this.x=t.x+(e.x-t.x)*i,this.y=t.y+(e.y-t.y)*i,this.z=t.z+(e.z-t.z)*i,this}cross(t){return this.crossVectors(this,t)}crossVectors(t,e){let i=t.x,s=t.y,r=t.z,o=e.x,a=e.y,l=e.z;return this.x=s*l-r*a,this.y=r*o-i*l,this.z=i*a-s*o,this}projectOnVector(t){let e=t.lengthSq();if(e===0)return this.set(0,0,0);let i=t.dot(this)/e;return this.copy(t).multiplyScalar(i)}projectOnPlane(t){return Da.copy(this).projectOnVector(t),this.sub(Da)}reflect(t){return this.sub(Da.copy(t).multiplyScalar(2*this.dot(t)))}angleTo(t){let e=Math.sqrt(this.lengthSq()*t.lengthSq());if(e===0)return Math.PI/2;let i=this.dot(t)/e;return Math.acos(Ce(i,-1,1))}distanceTo(t){return Math.sqrt(this.distanceToSquared(t))}distanceToSquared(t){let e=this.x-t.x,i=this.y-t.y,s=this.z-t.z;return e*e+i*i+s*s}manhattanDistanceTo(t){return Math.abs(this.x-t.x)+Math.abs(this.y-t.y)+Math.abs(this.z-t.z)}setFromSpherical(t){return this.setFromSphericalCoords(t.radius,t.phi,t.theta)}setFromSphericalCoords(t,e,i){let s=Math.sin(e)*t;return this.x=s*Math.sin(i),this.y=Math.cos(e)*t,this.z=s*Math.cos(i),this}setFromCylindrical(t){return this.setFromCylindricalCoords(t.radius,t.theta,t.y)}setFromCylindricalCoords(t,e,i){return this.x=t*Math.sin(e),this.y=i,this.z=t*Math.cos(e),this}setFromMatrixPosition(t){let e=t.elements;return this.x=e[12],this.y=e[13],this.z=e[14],this}setFromMatrixScale(t){let e=this.setFromMatrixColumn(t,0).length(),i=this.setFromMatrixColumn(t,1).length(),s=this.setFromMatrixColumn(t,2).length();return this.x=e,this.y=i,this.z=s,this}setFromMatrixColumn(t,e){return this.fromArray(t.elements,e*4)}setFromMatrix3Column(t,e){return this.fromArray(t.elements,e*3)}setFromEuler(t){return this.x=t._x,this.y=t._y,this.z=t._z,this}setFromColor(t){return this.x=t.r,this.y=t.g,this.z=t.b,this}equals(t){return t.x===this.x&&t.y===this.y&&t.z===this.z}fromArray(t,e=0){return this.x=t[e],this.y=t[e+1],this.z=t[e+2],this}toArray(t=[],e=0){return t[e]=this.x,t[e+1]=this.y,t[e+2]=this.z,t}fromBufferAttribute(t,e){return this.x=t.getX(e),this.y=t.getY(e),this.z=t.getZ(e),this}random(){return this.x=Math.random(),this.y=Math.random(),this.z=Math.random(),this}randomDirection(){let t=Math.random()*Math.PI*2,e=Math.random()*2-1,i=Math.sqrt(1-e*e);return this.x=i*Math.cos(t),this.y=e,this.z=i*Math.sin(t),this}*[Symbol.iterator](){yield this.x,yield this.y,yield this.z}},Da=new F,Wu=new Yn,mi=class{constructor(t=new F(1/0,1/0,1/0),e=new F(-1/0,-1/0,-1/0)){this.isBox3=!0,this.min=t,this.max=e}set(t,e){return this.min.copy(t),this.max.copy(e),this}setFromArray(t){this.makeEmpty();for(let e=0,i=t.length;e<i;e+=3)this.expandByPoint(Ke.fromArray(t,e));return this}setFromBufferAttribute(t){this.makeEmpty();for(let e=0,i=t.count;e<i;e++)this.expandByPoint(Ke.fromBufferAttribute(t,e));return this}setFromPoints(t){this.makeEmpty();for(let e=0,i=t.length;e<i;e++)this.expandByPoint(t[e]);return this}setFromCenterAndSize(t,e){let i=Ke.copy(e).multiplyScalar(.5);return this.min.copy(t).sub(i),this.max.copy(t).add(i),this}setFromObject(t,e=!1){return this.makeEmpty(),this.expandByObject(t,e)}clone(){return new this.constructor().copy(this)}copy(t){return this.min.copy(t.min),this.max.copy(t.max),this}makeEmpty(){return this.min.x=this.min.y=this.min.z=1/0,this.max.x=this.max.y=this.max.z=-1/0,this}isEmpty(){return this.max.x<this.min.x||this.max.y<this.min.y||this.max.z<this.min.z}getCenter(t){return this.isEmpty()?t.set(0,0,0):t.addVectors(this.min,this.max).multiplyScalar(.5)}getSize(t){return this.isEmpty()?t.set(0,0,0):t.subVectors(this.max,this.min)}expandByPoint(t){return this.min.min(t),this.max.max(t),this}expandByVector(t){return this.min.sub(t),this.max.add(t),this}expandByScalar(t){return this.min.addScalar(-t),this.max.addScalar(t),this}expandByObject(t,e=!1){t.updateWorldMatrix(!1,!1);let i=t.geometry;if(i!==void 0){let r=i.getAttribute("position");if(e===!0&&r!==void 0&&t.isInstancedMesh!==!0)for(let o=0,a=r.count;o<a;o++)t.isMesh===!0?t.getVertexPosition(o,Ke):Ke.fromBufferAttribute(r,o),Ke.applyMatrix4(t.matrixWorld),this.expandByPoint(Ke);else t.boundingBox!==void 0?(t.boundingBox===null&&t.computeBoundingBox(),Gr.copy(t.boundingBox)):(i.boundingBox===null&&i.computeBoundingBox(),Gr.copy(i.boundingBox)),Gr.applyMatrix4(t.matrixWorld),this.union(Gr)}let s=t.children;for(let r=0,o=s.length;r<o;r++)this.expandByObject(s[r],e);return this}containsPoint(t){return t.x>=this.min.x&&t.x<=this.max.x&&t.y>=this.min.y&&t.y<=this.max.y&&t.z>=this.min.z&&t.z<=this.max.z}containsBox(t){return this.min.x<=t.min.x&&t.max.x<=this.max.x&&this.min.y<=t.min.y&&t.max.y<=this.max.y&&this.min.z<=t.min.z&&t.max.z<=this.max.z}getParameter(t,e){return e.set((t.x-this.min.x)/(this.max.x-this.min.x),(t.y-this.min.y)/(this.max.y-this.min.y),(t.z-this.min.z)/(this.max.z-this.min.z))}intersectsBox(t){return t.max.x>=this.min.x&&t.min.x<=this.max.x&&t.max.y>=this.min.y&&t.min.y<=this.max.y&&t.max.z>=this.min.z&&t.min.z<=this.max.z}intersectsSphere(t){return this.clampPoint(t.center,Ke),Ke.distanceToSquared(t.center)<=t.radius*t.radius}intersectsPlane(t){let e,i;return t.normal.x>0?(e=t.normal.x*this.min.x,i=t.normal.x*this.max.x):(e=t.normal.x*this.max.x,i=t.normal.x*this.min.x),t.normal.y>0?(e+=t.normal.y*this.min.y,i+=t.normal.y*this.max.y):(e+=t.normal.y*this.max.y,i+=t.normal.y*this.min.y),t.normal.z>0?(e+=t.normal.z*this.min.z,i+=t.normal.z*this.max.z):(e+=t.normal.z*this.max.z,i+=t.normal.z*this.min.z),e<=-t.constant&&i>=-t.constant}intersectsTriangle(t){if(this.isEmpty())return!1;this.getCenter(Ds),Wr.subVectors(this.max,Ds),Wi.subVectors(t.a,Ds),Xi.subVectors(t.b,Ds),qi.subVectors(t.c,Ds),Fn.subVectors(Xi,Wi),On.subVectors(qi,Xi),ni.subVectors(Wi,qi);let e=[0,-Fn.z,Fn.y,0,-On.z,On.y,0,-ni.z,ni.y,Fn.z,0,-Fn.x,On.z,0,-On.x,ni.z,0,-ni.x,-Fn.y,Fn.x,0,-On.y,On.x,0,-ni.y,ni.x,0];return!Ua(e,Wi,Xi,qi,Wr)||(e=[1,0,0,0,1,0,0,0,1],!Ua(e,Wi,Xi,qi,Wr))?!1:(Xr.crossVectors(Fn,On),e=[Xr.x,Xr.y,Xr.z],Ua(e,Wi,Xi,qi,Wr))}clampPoint(t,e){return e.copy(t).clamp(this.min,this.max)}distanceToPoint(t){return this.clampPoint(t,Ke).distanceTo(t)}getBoundingSphere(t){return this.isEmpty()?t.makeEmpty():(this.getCenter(t.center),t.radius=this.getSize(Ke).length()*.5),t}intersect(t){return this.min.max(t.min),this.max.min(t.max),this.isEmpty()&&this.makeEmpty(),this}union(t){return this.min.min(t.min),this.max.max(t.max),this}applyMatrix4(t){return this.isEmpty()?this:(pn[0].set(this.min.x,this.min.y,this.min.z).applyMatrix4(t),pn[1].set(this.min.x,this.min.y,this.max.z).applyMatrix4(t),pn[2].set(this.min.x,this.max.y,this.min.z).applyMatrix4(t),pn[3].set(this.min.x,this.max.y,this.max.z).applyMatrix4(t),pn[4].set(this.max.x,this.min.y,this.min.z).applyMatrix4(t),pn[5].set(this.max.x,this.min.y,this.max.z).applyMatrix4(t),pn[6].set(this.max.x,this.max.y,this.min.z).applyMatrix4(t),pn[7].set(this.max.x,this.max.y,this.max.z).applyMatrix4(t),this.setFromPoints(pn),this)}translate(t){return this.min.add(t),this.max.add(t),this}equals(t){return t.min.equals(this.min)&&t.max.equals(this.max)}},pn=[new F,new F,new F,new F,new F,new F,new F,new F],Ke=new F,Gr=new mi,Wi=new F,Xi=new F,qi=new F,Fn=new F,On=new F,ni=new F,Ds=new F,Wr=new F,Xr=new F,ii=new F;function Ua(n,t,e,i,s){for(let r=0,o=n.length-3;r<=o;r+=3){ii.fromArray(n,r);let a=s.x*Math.abs(ii.x)+s.y*Math.abs(ii.y)+s.z*Math.abs(ii.z),l=t.dot(ii),c=e.dot(ii),h=i.dot(ii);if(Math.max(-Math.max(l,c,h),Math.min(l,c,h))>a)return!1}return!0}var Im=new mi,Us=new F,Na=new F,gi=class{constructor(t=new F,e=-1){this.isSphere=!0,this.center=t,this.radius=e}set(t,e){return this.center.copy(t),this.radius=e,this}setFromPoints(t,e){let i=this.center;e!==void 0?i.copy(e):Im.setFromPoints(t).getCenter(i);let s=0;for(let r=0,o=t.length;r<o;r++)s=Math.max(s,i.distanceToSquared(t[r]));return this.radius=Math.sqrt(s),this}copy(t){return this.center.copy(t.center),this.radius=t.radius,this}isEmpty(){return this.radius<0}makeEmpty(){return this.center.set(0,0,0),this.radius=-1,this}containsPoint(t){return t.distanceToSquared(this.center)<=this.radius*this.radius}distanceToPoint(t){return t.distanceTo(this.center)-this.radius}intersectsSphere(t){let e=this.radius+t.radius;return t.center.distanceToSquared(this.center)<=e*e}intersectsBox(t){return t.intersectsSphere(this)}intersectsPlane(t){return Math.abs(t.distanceToPoint(this.center))<=this.radius}clampPoint(t,e){let i=this.center.distanceToSquared(t);return e.copy(t),i>this.radius*this.radius&&(e.sub(this.center).normalize(),e.multiplyScalar(this.radius).add(this.center)),e}getBoundingBox(t){return this.isEmpty()?(t.makeEmpty(),t):(t.set(this.center,this.center),t.expandByScalar(this.radius),t)}applyMatrix4(t){return this.center.applyMatrix4(t),this.radius=this.radius*t.getMaxScaleOnAxis(),this}translate(t){return this.center.add(t),this}expandByPoint(t){if(this.isEmpty())return this.center.copy(t),this.radius=0,this;Us.subVectors(t,this.center);let e=Us.lengthSq();if(e>this.radius*this.radius){let i=Math.sqrt(e),s=(i-this.radius)*.5;this.center.addScaledVector(Us,s/i),this.radius+=s}return this}union(t){return t.isEmpty()?this:this.isEmpty()?(this.copy(t),this):(this.center.equals(t.center)===!0?this.radius=Math.max(this.radius,t.radius):(Na.subVectors(t.center,this.center).setLength(t.radius),this.expandByPoint(Us.copy(t.center).add(Na)),this.expandByPoint(Us.copy(t.center).sub(Na))),this)}equals(t){return t.center.equals(this.center)&&t.radius===this.radius}clone(){return new this.constructor().copy(this)}},mn=new F,Fa=new F,qr=new F,Bn=new F,Oa=new F,Yr=new F,Ba=new F,ks=class{constructor(t=new F,e=new F(0,0,-1)){this.origin=t,this.direction=e}set(t,e){return this.origin.copy(t),this.direction.copy(e),this}copy(t){return this.origin.copy(t.origin),this.direction.copy(t.direction),this}at(t,e){return e.copy(this.origin).addScaledVector(this.direction,t)}lookAt(t){return this.direction.copy(t).sub(this.origin).normalize(),this}recast(t){return this.origin.copy(this.at(t,mn)),this}closestPointToPoint(t,e){e.subVectors(t,this.origin);let i=e.dot(this.direction);return i<0?e.copy(this.origin):e.copy(this.origin).addScaledVector(this.direction,i)}distanceToPoint(t){return Math.sqrt(this.distanceSqToPoint(t))}distanceSqToPoint(t){let e=mn.subVectors(t,this.origin).dot(this.direction);return e<0?this.origin.distanceToSquared(t):(mn.copy(this.origin).addScaledVector(this.direction,e),mn.distanceToSquared(t))}distanceSqToSegment(t,e,i,s){Fa.copy(t).add(e).multiplyScalar(.5),qr.copy(e).sub(t).normalize(),Bn.copy(this.origin).sub(Fa);let r=t.distanceTo(e)*.5,o=-this.direction.dot(qr),a=Bn.dot(this.direction),l=-Bn.dot(qr),c=Bn.lengthSq(),h=Math.abs(1-o*o),d,p,m,g;if(h>0)if(d=o*l-a,p=o*a-l,g=r*h,d>=0)if(p>=-g)if(p<=g){let _=1/h;d*=_,p*=_,m=d*(d+o*p+2*a)+p*(o*d+p+2*l)+c}else p=r,d=Math.max(0,-(o*p+a)),m=-d*d+p*(p+2*l)+c;else p=-r,d=Math.max(0,-(o*p+a)),m=-d*d+p*(p+2*l)+c;else p<=-g?(d=Math.max(0,-(-o*r+a)),p=d>0?-r:Math.min(Math.max(-r,-l),r),m=-d*d+p*(p+2*l)+c):p<=g?(d=0,p=Math.min(Math.max(-r,-l),r),m=p*(p+2*l)+c):(d=Math.max(0,-(o*r+a)),p=d>0?r:Math.min(Math.max(-r,-l),r),m=-d*d+p*(p+2*l)+c);else p=o>0?-r:r,d=Math.max(0,-(o*p+a)),m=-d*d+p*(p+2*l)+c;return i&&i.copy(this.origin).addScaledVector(this.direction,d),s&&s.copy(Fa).addScaledVector(qr,p),m}intersectSphere(t,e){mn.subVectors(t.center,this.origin);let i=mn.dot(this.direction),s=mn.dot(mn)-i*i,r=t.radius*t.radius;if(s>r)return null;let o=Math.sqrt(r-s),a=i-o,l=i+o;return l<0?null:a<0?this.at(l,e):this.at(a,e)}intersectsSphere(t){return this.distanceSqToPoint(t.center)<=t.radius*t.radius}distanceToPlane(t){let e=t.normal.dot(this.direction);if(e===0)return t.distanceToPoint(this.origin)===0?0:null;let i=-(this.origin.dot(t.normal)+t.constant)/e;return i>=0?i:null}intersectPlane(t,e){let i=this.distanceToPlane(t);return i===null?null:this.at(i,e)}intersectsPlane(t){let e=t.distanceToPoint(this.origin);return e===0||t.normal.dot(this.direction)*e<0}intersectBox(t,e){let i,s,r,o,a,l,c=1/this.direction.x,h=1/this.direction.y,d=1/this.direction.z,p=this.origin;return c>=0?(i=(t.min.x-p.x)*c,s=(t.max.x-p.x)*c):(i=(t.max.x-p.x)*c,s=(t.min.x-p.x)*c),h>=0?(r=(t.min.y-p.y)*h,o=(t.max.y-p.y)*h):(r=(t.max.y-p.y)*h,o=(t.min.y-p.y)*h),i>o||r>s||((r>i||isNaN(i))&&(i=r),(o<s||isNaN(s))&&(s=o),d>=0?(a=(t.min.z-p.z)*d,l=(t.max.z-p.z)*d):(a=(t.max.z-p.z)*d,l=(t.min.z-p.z)*d),i>l||a>s)||((a>i||i!==i)&&(i=a),(l<s||s!==s)&&(s=l),s<0)?null:this.at(i>=0?i:s,e)}intersectsBox(t){return this.intersectBox(t,mn)!==null}intersectTriangle(t,e,i,s,r){Oa.subVectors(e,t),Yr.subVectors(i,t),Ba.crossVectors(Oa,Yr);let o=this.direction.dot(Ba),a;if(o>0){if(s)return null;a=1}else if(o<0)a=-1,o=-o;else return null;Bn.subVectors(this.origin,t);let l=a*this.direction.dot(Yr.crossVectors(Bn,Yr));if(l<0)return null;let c=a*this.direction.dot(Oa.cross(Bn));if(c<0||l+c>o)return null;let h=-a*Bn.dot(Ba);return h<0?null:this.at(h/o,r)}applyMatrix4(t){return this.origin.applyMatrix4(t),this.direction.transformDirection(t),this}equals(t){return t.origin.equals(this.origin)&&t.direction.equals(this.direction)}clone(){return new this.constructor().copy(this)}},le=class n{constructor(t,e,i,s,r,o,a,l,c,h,d,p,m,g,_,u){n.prototype.isMatrix4=!0,this.elements=[1,0,0,0,0,1,0,0,0,0,1,0,0,0,0,1],t!==void 0&&this.set(t,e,i,s,r,o,a,l,c,h,d,p,m,g,_,u)}set(t,e,i,s,r,o,a,l,c,h,d,p,m,g,_,u){let f=this.elements;return f[0]=t,f[4]=e,f[8]=i,f[12]=s,f[1]=r,f[5]=o,f[9]=a,f[13]=l,f[2]=c,f[6]=h,f[10]=d,f[14]=p,f[3]=m,f[7]=g,f[11]=_,f[15]=u,this}identity(){return this.set(1,0,0,0,0,1,0,0,0,0,1,0,0,0,0,1),this}clone(){return new n().fromArray(this.elements)}copy(t){let e=this.elements,i=t.elements;return e[0]=i[0],e[1]=i[1],e[2]=i[2],e[3]=i[3],e[4]=i[4],e[5]=i[5],e[6]=i[6],e[7]=i[7],e[8]=i[8],e[9]=i[9],e[10]=i[10],e[11]=i[11],e[12]=i[12],e[13]=i[13],e[14]=i[14],e[15]=i[15],this}copyPosition(t){let e=this.elements,i=t.elements;return e[12]=i[12],e[13]=i[13],e[14]=i[14],this}setFromMatrix3(t){let e=t.elements;return this.set(e[0],e[3],e[6],0,e[1],e[4],e[7],0,e[2],e[5],e[8],0,0,0,0,1),this}extractBasis(t,e,i){return t.setFromMatrixColumn(this,0),e.setFromMatrixColumn(this,1),i.setFromMatrixColumn(this,2),this}makeBasis(t,e,i){return this.set(t.x,e.x,i.x,0,t.y,e.y,i.y,0,t.z,e.z,i.z,0,0,0,0,1),this}extractRotation(t){let e=this.elements,i=t.elements,s=1/Yi.setFromMatrixColumn(t,0).length(),r=1/Yi.setFromMatrixColumn(t,1).length(),o=1/Yi.setFromMatrixColumn(t,2).length();return e[0]=i[0]*s,e[1]=i[1]*s,e[2]=i[2]*s,e[3]=0,e[4]=i[4]*r,e[5]=i[5]*r,e[6]=i[6]*r,e[7]=0,e[8]=i[8]*o,e[9]=i[9]*o,e[10]=i[10]*o,e[11]=0,e[12]=0,e[13]=0,e[14]=0,e[15]=1,this}makeRotationFromEuler(t){let e=this.elements,i=t.x,s=t.y,r=t.z,o=Math.cos(i),a=Math.sin(i),l=Math.cos(s),c=Math.sin(s),h=Math.cos(r),d=Math.sin(r);if(t.order==="XYZ"){let p=o*h,m=o*d,g=a*h,_=a*d;e[0]=l*h,e[4]=-l*d,e[8]=c,e[1]=m+g*c,e[5]=p-_*c,e[9]=-a*l,e[2]=_-p*c,e[6]=g+m*c,e[10]=o*l}else if(t.order==="YXZ"){let p=l*h,m=l*d,g=c*h,_=c*d;e[0]=p+_*a,e[4]=g*a-m,e[8]=o*c,e[1]=o*d,e[5]=o*h,e[9]=-a,e[2]=m*a-g,e[6]=_+p*a,e[10]=o*l}else if(t.order==="ZXY"){let p=l*h,m=l*d,g=c*h,_=c*d;e[0]=p-_*a,e[4]=-o*d,e[8]=g+m*a,e[1]=m+g*a,e[5]=o*h,e[9]=_-p*a,e[2]=-o*c,e[6]=a,e[10]=o*l}else if(t.order==="ZYX"){let p=o*h,m=o*d,g=a*h,_=a*d;e[0]=l*h,e[4]=g*c-m,e[8]=p*c+_,e[1]=l*d,e[5]=_*c+p,e[9]=m*c-g,e[2]=-c,e[6]=a*l,e[10]=o*l}else if(t.order==="YZX"){let p=o*l,m=o*c,g=a*l,_=a*c;e[0]=l*h,e[4]=_-p*d,e[8]=g*d+m,e[1]=d,e[5]=o*h,e[9]=-a*h,e[2]=-c*h,e[6]=m*d+g,e[10]=p-_*d}else if(t.order==="XZY"){let p=o*l,m=o*c,g=a*l,_=a*c;e[0]=l*h,e[4]=-d,e[8]=c*h,e[1]=p*d+_,e[5]=o*h,e[9]=m*d-g,e[2]=g*d-m,e[6]=a*h,e[10]=_*d+p}return e[3]=0,e[7]=0,e[11]=0,e[12]=0,e[13]=0,e[14]=0,e[15]=1,this}makeRotationFromQuaternion(t){return this.compose(Lm,t,Dm)}lookAt(t,e,i){let s=this.elements;return Ue.subVectors(t,e),Ue.lengthSq()===0&&(Ue.z=1),Ue.normalize(),Vn.crossVectors(i,Ue),Vn.lengthSq()===0&&(Math.abs(i.z)===1?Ue.x+=1e-4:Ue.z+=1e-4,Ue.normalize(),Vn.crossVectors(i,Ue)),Vn.normalize(),Kr.crossVectors(Ue,Vn),s[0]=Vn.x,s[4]=Kr.x,s[8]=Ue.x,s[1]=Vn.y,s[5]=Kr.y,s[9]=Ue.y,s[2]=Vn.z,s[6]=Kr.z,s[10]=Ue.z,this}multiply(t){return this.multiplyMatrices(this,t)}premultiply(t){return this.multiplyMatrices(t,this)}multiplyMatrices(t,e){let i=t.elements,s=e.elements,r=this.elements,o=i[0],a=i[4],l=i[8],c=i[12],h=i[1],d=i[5],p=i[9],m=i[13],g=i[2],_=i[6],u=i[10],f=i[14],b=i[3],S=i[7],E=i[11],C=i[15],A=s[0],T=s[4],P=s[8],G=s[12],x=s[1],y=s[5],V=s[9],U=s[13],H=s[2],$=s[6],k=s[10],Z=s[14],z=s[3],it=s[7],ot=s[11],pt=s[15];return r[0]=o*A+a*x+l*H+c*z,r[4]=o*T+a*y+l*$+c*it,r[8]=o*P+a*V+l*k+c*ot,r[12]=o*G+a*U+l*Z+c*pt,r[1]=h*A+d*x+p*H+m*z,r[5]=h*T+d*y+p*$+m*it,r[9]=h*P+d*V+p*k+m*ot,r[13]=h*G+d*U+p*Z+m*pt,r[2]=g*A+_*x+u*H+f*z,r[6]=g*T+_*y+u*$+f*it,r[10]=g*P+_*V+u*k+f*ot,r[14]=g*G+_*U+u*Z+f*pt,r[3]=b*A+S*x+E*H+C*z,r[7]=b*T+S*y+E*$+C*it,r[11]=b*P+S*V+E*k+C*ot,r[15]=b*G+S*U+E*Z+C*pt,this}multiplyScalar(t){let e=this.elements;return e[0]*=t,e[4]*=t,e[8]*=t,e[12]*=t,e[1]*=t,e[5]*=t,e[9]*=t,e[13]*=t,e[2]*=t,e[6]*=t,e[10]*=t,e[14]*=t,e[3]*=t,e[7]*=t,e[11]*=t,e[15]*=t,this}determinant(){let t=this.elements,e=t[0],i=t[4],s=t[8],r=t[12],o=t[1],a=t[5],l=t[9],c=t[13],h=t[2],d=t[6],p=t[10],m=t[14],g=t[3],_=t[7],u=t[11],f=t[15];return g*(+r*l*d-s*c*d-r*a*p+i*c*p+s*a*m-i*l*m)+_*(+e*l*m-e*c*p+r*o*p-s*o*m+s*c*h-r*l*h)+u*(+e*c*d-e*a*m-r*o*d+i*o*m+r*a*h-i*c*h)+f*(-s*a*h-e*l*d+e*a*p+s*o*d-i*o*p+i*l*h)}transpose(){let t=this.elements,e;return e=t[1],t[1]=t[4],t[4]=e,e=t[2],t[2]=t[8],t[8]=e,e=t[6],t[6]=t[9],t[9]=e,e=t[3],t[3]=t[12],t[12]=e,e=t[7],t[7]=t[13],t[13]=e,e=t[11],t[11]=t[14],t[14]=e,this}setPosition(t,e,i){let s=this.elements;return t.isVector3?(s[12]=t.x,s[13]=t.y,s[14]=t.z):(s[12]=t,s[13]=e,s[14]=i),this}invert(){let t=this.elements,e=t[0],i=t[1],s=t[2],r=t[3],o=t[4],a=t[5],l=t[6],c=t[7],h=t[8],d=t[9],p=t[10],m=t[11],g=t[12],_=t[13],u=t[14],f=t[15],b=d*u*c-_*p*c+_*l*m-a*u*m-d*l*f+a*p*f,S=g*p*c-h*u*c-g*l*m+o*u*m+h*l*f-o*p*f,E=h*_*c-g*d*c+g*a*m-o*_*m-h*a*f+o*d*f,C=g*d*l-h*_*l-g*a*p+o*_*p+h*a*u-o*d*u,A=e*b+i*S+s*E+r*C;if(A===0)return this.set(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);let T=1/A;return t[0]=b*T,t[1]=(_*p*r-d*u*r-_*s*m+i*u*m+d*s*f-i*p*f)*T,t[2]=(a*u*r-_*l*r+_*s*c-i*u*c-a*s*f+i*l*f)*T,t[3]=(d*l*r-a*p*r-d*s*c+i*p*c+a*s*m-i*l*m)*T,t[4]=S*T,t[5]=(h*u*r-g*p*r+g*s*m-e*u*m-h*s*f+e*p*f)*T,t[6]=(g*l*r-o*u*r-g*s*c+e*u*c+o*s*f-e*l*f)*T,t[7]=(o*p*r-h*l*r+h*s*c-e*p*c-o*s*m+e*l*m)*T,t[8]=E*T,t[9]=(g*d*r-h*_*r-g*i*m+e*_*m+h*i*f-e*d*f)*T,t[10]=(o*_*r-g*a*r+g*i*c-e*_*c-o*i*f+e*a*f)*T,t[11]=(h*a*r-o*d*r-h*i*c+e*d*c+o*i*m-e*a*m)*T,t[12]=C*T,t[13]=(h*_*s-g*d*s+g*i*p-e*_*p-h*i*u+e*d*u)*T,t[14]=(g*a*s-o*_*s-g*i*l+e*_*l+o*i*u-e*a*u)*T,t[15]=(o*d*s-h*a*s+h*i*l-e*d*l-o*i*p+e*a*p)*T,this}scale(t){let e=this.elements,i=t.x,s=t.y,r=t.z;return e[0]*=i,e[4]*=s,e[8]*=r,e[1]*=i,e[5]*=s,e[9]*=r,e[2]*=i,e[6]*=s,e[10]*=r,e[3]*=i,e[7]*=s,e[11]*=r,this}getMaxScaleOnAxis(){let t=this.elements,e=t[0]*t[0]+t[1]*t[1]+t[2]*t[2],i=t[4]*t[4]+t[5]*t[5]+t[6]*t[6],s=t[8]*t[8]+t[9]*t[9]+t[10]*t[10];return Math.sqrt(Math.max(e,i,s))}makeTranslation(t,e,i){return t.isVector3?this.set(1,0,0,t.x,0,1,0,t.y,0,0,1,t.z,0,0,0,1):this.set(1,0,0,t,0,1,0,e,0,0,1,i,0,0,0,1),this}makeRotationX(t){let e=Math.cos(t),i=Math.sin(t);return this.set(1,0,0,0,0,e,-i,0,0,i,e,0,0,0,0,1),this}makeRotationY(t){let e=Math.cos(t),i=Math.sin(t);return this.set(e,0,i,0,0,1,0,0,-i,0,e,0,0,0,0,1),this}makeRotationZ(t){let e=Math.cos(t),i=Math.sin(t);return this.set(e,-i,0,0,i,e,0,0,0,0,1,0,0,0,0,1),this}makeRotationAxis(t,e){let i=Math.cos(e),s=Math.sin(e),r=1-i,o=t.x,a=t.y,l=t.z,c=r*o,h=r*a;return this.set(c*o+i,c*a-s*l,c*l+s*a,0,c*a+s*l,h*a+i,h*l-s*o,0,c*l-s*a,h*l+s*o,r*l*l+i,0,0,0,0,1),this}makeScale(t,e,i){return this.set(t,0,0,0,0,e,0,0,0,0,i,0,0,0,0,1),this}makeShear(t,e,i,s,r,o){return this.set(1,i,r,0,t,1,o,0,e,s,1,0,0,0,0,1),this}compose(t,e,i){let s=this.elements,r=e._x,o=e._y,a=e._z,l=e._w,c=r+r,h=o+o,d=a+a,p=r*c,m=r*h,g=r*d,_=o*h,u=o*d,f=a*d,b=l*c,S=l*h,E=l*d,C=i.x,A=i.y,T=i.z;return s[0]=(1-(_+f))*C,s[1]=(m+E)*C,s[2]=(g-S)*C,s[3]=0,s[4]=(m-E)*A,s[5]=(1-(p+f))*A,s[6]=(u+b)*A,s[7]=0,s[8]=(g+S)*T,s[9]=(u-b)*T,s[10]=(1-(p+_))*T,s[11]=0,s[12]=t.x,s[13]=t.y,s[14]=t.z,s[15]=1,this}decompose(t,e,i){let s=this.elements,r=Yi.set(s[0],s[1],s[2]).length(),o=Yi.set(s[4],s[5],s[6]).length(),a=Yi.set(s[8],s[9],s[10]).length();this.determinant()<0&&(r=-r),t.x=s[12],t.y=s[13],t.z=s[14],$e.copy(this);let c=1/r,h=1/o,d=1/a;return $e.elements[0]*=c,$e.elements[1]*=c,$e.elements[2]*=c,$e.elements[4]*=h,$e.elements[5]*=h,$e.elements[6]*=h,$e.elements[8]*=d,$e.elements[9]*=d,$e.elements[10]*=d,e.setFromRotationMatrix($e),i.x=r,i.y=o,i.z=a,this}makePerspective(t,e,i,s,r,o,a=Sn){let l=this.elements,c=2*r/(e-t),h=2*r/(i-s),d=(e+t)/(e-t),p=(i+s)/(i-s),m,g;if(a===Sn)m=-(o+r)/(o-r),g=-2*o*r/(o-r);else if(a===To)m=-o/(o-r),g=-o*r/(o-r);else throw new Error("THREE.Matrix4.makePerspective(): Invalid coordinate system: "+a);return l[0]=c,l[4]=0,l[8]=d,l[12]=0,l[1]=0,l[5]=h,l[9]=p,l[13]=0,l[2]=0,l[6]=0,l[10]=m,l[14]=g,l[3]=0,l[7]=0,l[11]=-1,l[15]=0,this}makeOrthographic(t,e,i,s,r,o,a=Sn){let l=this.elements,c=1/(e-t),h=1/(i-s),d=1/(o-r),p=(e+t)*c,m=(i+s)*h,g,_;if(a===Sn)g=(o+r)*d,_=-2*d;else if(a===To)g=r*d,_=-1*d;else throw new Error("THREE.Matrix4.makeOrthographic(): Invalid coordinate system: "+a);return l[0]=2*c,l[4]=0,l[8]=0,l[12]=-p,l[1]=0,l[5]=2*h,l[9]=0,l[13]=-m,l[2]=0,l[6]=0,l[10]=_,l[14]=-g,l[3]=0,l[7]=0,l[11]=0,l[15]=1,this}equals(t){let e=this.elements,i=t.elements;for(let s=0;s<16;s++)if(e[s]!==i[s])return!1;return!0}fromArray(t,e=0){for(let i=0;i<16;i++)this.elements[i]=t[i+e];return this}toArray(t=[],e=0){let i=this.elements;return t[e]=i[0],t[e+1]=i[1],t[e+2]=i[2],t[e+3]=i[3],t[e+4]=i[4],t[e+5]=i[5],t[e+6]=i[6],t[e+7]=i[7],t[e+8]=i[8],t[e+9]=i[9],t[e+10]=i[10],t[e+11]=i[11],t[e+12]=i[12],t[e+13]=i[13],t[e+14]=i[14],t[e+15]=i[15],t}},Yi=new F,$e=new le,Lm=new F(0,0,0),Dm=new F(1,1,1),Vn=new F,Kr=new F,Ue=new F,Xu=new le,qu=new Yn,wn=class n{constructor(t=0,e=0,i=0,s=n.DEFAULT_ORDER){this.isEuler=!0,this._x=t,this._y=e,this._z=i,this._order=s}get x(){return this._x}set x(t){this._x=t,this._onChangeCallback()}get y(){return this._y}set y(t){this._y=t,this._onChangeCallback()}get z(){return this._z}set z(t){this._z=t,this._onChangeCallback()}get order(){return this._order}set order(t){this._order=t,this._onChangeCallback()}set(t,e,i,s=this._order){return this._x=t,this._y=e,this._z=i,this._order=s,this._onChangeCallback(),this}clone(){return new this.constructor(this._x,this._y,this._z,this._order)}copy(t){return this._x=t._x,this._y=t._y,this._z=t._z,this._order=t._order,this._onChangeCallback(),this}setFromRotationMatrix(t,e=this._order,i=!0){let s=t.elements,r=s[0],o=s[4],a=s[8],l=s[1],c=s[5],h=s[9],d=s[2],p=s[6],m=s[10];switch(e){case"XYZ":this._y=Math.asin(Ce(a,-1,1)),Math.abs(a)<.9999999?(this._x=Math.atan2(-h,m),this._z=Math.atan2(-o,r)):(this._x=Math.atan2(p,c),this._z=0);break;case"YXZ":this._x=Math.asin(-Ce(h,-1,1)),Math.abs(h)<.9999999?(this._y=Math.atan2(a,m),this._z=Math.atan2(l,c)):(this._y=Math.atan2(-d,r),this._z=0);break;case"ZXY":this._x=Math.asin(Ce(p,-1,1)),Math.abs(p)<.9999999?(this._y=Math.atan2(-d,m),this._z=Math.atan2(-o,c)):(this._y=0,this._z=Math.atan2(l,r));break;case"ZYX":this._y=Math.asin(-Ce(d,-1,1)),Math.abs(d)<.9999999?(this._x=Math.atan2(p,m),this._z=Math.atan2(l,r)):(this._x=0,this._z=Math.atan2(-o,c));break;case"YZX":this._z=Math.asin(Ce(l,-1,1)),Math.abs(l)<.9999999?(this._x=Math.atan2(-h,c),this._y=Math.atan2(-d,r)):(this._x=0,this._y=Math.atan2(a,m));break;case"XZY":this._z=Math.asin(-Ce(o,-1,1)),Math.abs(o)<.9999999?(this._x=Math.atan2(p,c),this._y=Math.atan2(a,r)):(this._x=Math.atan2(-h,m),this._y=0);break;default:console.warn("THREE.Euler: .setFromRotationMatrix() encountered an unknown order: "+e)}return this._order=e,i===!0&&this._onChangeCallback(),this}setFromQuaternion(t,e,i){return Xu.makeRotationFromQuaternion(t),this.setFromRotationMatrix(Xu,e,i)}setFromVector3(t,e=this._order){return this.set(t.x,t.y,t.z,e)}reorder(t){return qu.setFromEuler(this),this.setFromQuaternion(qu,t)}equals(t){return t._x===this._x&&t._y===this._y&&t._z===this._z&&t._order===this._order}fromArray(t){return this._x=t[0],this._y=t[1],this._z=t[2],t[3]!==void 0&&(this._order=t[3]),this._onChangeCallback(),this}toArray(t=[],e=0){return t[e]=this._x,t[e+1]=this._y,t[e+2]=this._z,t[e+3]=this._order,t}_onChange(t){return this._onChangeCallback=t,this}_onChangeCallback(){}*[Symbol.iterator](){yield this._x,yield this._y,yield this._z,yield this._order}};wn.DEFAULT_ORDER="XYZ";var Po=class{constructor(){this.mask=1}set(t){this.mask=(1<<t|0)>>>0}enable(t){this.mask|=1<<t|0}enableAll(){this.mask=-1}toggle(t){this.mask^=1<<t|0}disable(t){this.mask&=~(1<<t|0)}disableAll(){this.mask=0}test(t){return(this.mask&t.mask)!==0}isEnabled(t){return(this.mask&(1<<t|0))!==0}},Um=0,Yu=new F,Ki=new Yn,gn=new le,$r=new F,Ns=new F,Nm=new F,Fm=new Yn,Ku=new F(1,0,0),$u=new F(0,1,0),Zu=new F(0,0,1),Ju={type:"added"},Om={type:"removed"},$i={type:"childadded",child:null},Va={type:"childremoved",child:null},Oe=class n extends qn{constructor(){super(),this.isObject3D=!0,Object.defineProperty(this,"id",{value:Um++}),this.uuid=qs(),this.name="",this.type="Object3D",this.parent=null,this.children=[],this.up=n.DEFAULT_UP.clone();let t=new F,e=new wn,i=new Yn,s=new F(1,1,1);function r(){i.setFromEuler(e,!1)}function o(){e.setFromQuaternion(i,void 0,!1)}e._onChange(r),i._onChange(o),Object.defineProperties(this,{position:{configurable:!0,enumerable:!0,value:t},rotation:{configurable:!0,enumerable:!0,value:e},quaternion:{configurable:!0,enumerable:!0,value:i},scale:{configurable:!0,enumerable:!0,value:s},modelViewMatrix:{value:new le},normalMatrix:{value:new Ft}}),this.matrix=new le,this.matrixWorld=new le,this.matrixAutoUpdate=n.DEFAULT_MATRIX_AUTO_UPDATE,this.matrixWorldAutoUpdate=n.DEFAULT_MATRIX_WORLD_AUTO_UPDATE,this.matrixWorldNeedsUpdate=!1,this.layers=new Po,this.visible=!0,this.castShadow=!1,this.receiveShadow=!1,this.frustumCulled=!0,this.renderOrder=0,this.animations=[],this.userData={}}onBeforeShadow(){}onAfterShadow(){}onBeforeRender(){}onAfterRender(){}applyMatrix4(t){this.matrixAutoUpdate&&this.updateMatrix(),this.matrix.premultiply(t),this.matrix.decompose(this.position,this.quaternion,this.scale)}applyQuaternion(t){return this.quaternion.premultiply(t),this}setRotationFromAxisAngle(t,e){this.quaternion.setFromAxisAngle(t,e)}setRotationFromEuler(t){this.quaternion.setFromEuler(t,!0)}setRotationFromMatrix(t){this.quaternion.setFromRotationMatrix(t)}setRotationFromQuaternion(t){this.quaternion.copy(t)}rotateOnAxis(t,e){return Ki.setFromAxisAngle(t,e),this.quaternion.multiply(Ki),this}rotateOnWorldAxis(t,e){return Ki.setFromAxisAngle(t,e),this.quaternion.premultiply(Ki),this}rotateX(t){return this.rotateOnAxis(Ku,t)}rotateY(t){return this.rotateOnAxis($u,t)}rotateZ(t){return this.rotateOnAxis(Zu,t)}translateOnAxis(t,e){return Yu.copy(t).applyQuaternion(this.quaternion),this.position.add(Yu.multiplyScalar(e)),this}translateX(t){return this.translateOnAxis(Ku,t)}translateY(t){return this.translateOnAxis($u,t)}translateZ(t){return this.translateOnAxis(Zu,t)}localToWorld(t){return this.updateWorldMatrix(!0,!1),t.applyMatrix4(this.matrixWorld)}worldToLocal(t){return this.updateWorldMatrix(!0,!1),t.applyMatrix4(gn.copy(this.matrixWorld).invert())}lookAt(t,e,i){t.isVector3?$r.copy(t):$r.set(t,e,i);let s=this.parent;this.updateWorldMatrix(!0,!1),Ns.setFromMatrixPosition(this.matrixWorld),this.isCamera||this.isLight?gn.lookAt(Ns,$r,this.up):gn.lookAt($r,Ns,this.up),this.quaternion.setFromRotationMatrix(gn),s&&(gn.extractRotation(s.matrixWorld),Ki.setFromRotationMatrix(gn),this.quaternion.premultiply(Ki.invert()))}add(t){if(arguments.length>1){for(let e=0;e<arguments.length;e++)this.add(arguments[e]);return this}return t===this?(console.error("THREE.Object3D.add: object can't be added as a child of itself.",t),this):(t&&t.isObject3D?(t.removeFromParent(),t.parent=this,this.children.push(t),t.dispatchEvent(Ju),$i.child=t,this.dispatchEvent($i),$i.child=null):console.error("THREE.Object3D.add: object not an instance of THREE.Object3D.",t),this)}remove(t){if(arguments.length>1){for(let i=0;i<arguments.length;i++)this.remove(arguments[i]);return this}let e=this.children.indexOf(t);return e!==-1&&(t.parent=null,this.children.splice(e,1),t.dispatchEvent(Om),Va.child=t,this.dispatchEvent(Va),Va.child=null),this}removeFromParent(){let t=this.parent;return t!==null&&t.remove(this),this}clear(){return this.remove(...this.children)}attach(t){return this.updateWorldMatrix(!0,!1),gn.copy(this.matrixWorld).invert(),t.parent!==null&&(t.parent.updateWorldMatrix(!0,!1),gn.multiply(t.parent.matrixWorld)),t.applyMatrix4(gn),t.removeFromParent(),t.parent=this,this.children.push(t),t.updateWorldMatrix(!1,!0),t.dispatchEvent(Ju),$i.child=t,this.dispatchEvent($i),$i.child=null,this}getObjectById(t){return this.getObjectByProperty("id",t)}getObjectByName(t){return this.getObjectByProperty("name",t)}getObjectByProperty(t,e){if(this[t]===e)return this;for(let i=0,s=this.children.length;i<s;i++){let o=this.children[i].getObjectByProperty(t,e);if(o!==void 0)return o}}getObjectsByProperty(t,e,i=[]){this[t]===e&&i.push(this);let s=this.children;for(let r=0,o=s.length;r<o;r++)s[r].getObjectsByProperty(t,e,i);return i}getWorldPosition(t){return this.updateWorldMatrix(!0,!1),t.setFromMatrixPosition(this.matrixWorld)}getWorldQuaternion(t){return this.updateWorldMatrix(!0,!1),this.matrixWorld.decompose(Ns,t,Nm),t}getWorldScale(t){return this.updateWorldMatrix(!0,!1),this.matrixWorld.decompose(Ns,Fm,t),t}getWorldDirection(t){this.updateWorldMatrix(!0,!1);let e=this.matrixWorld.elements;return t.set(e[8],e[9],e[10]).normalize()}raycast(){}traverse(t){t(this);let e=this.children;for(let i=0,s=e.length;i<s;i++)e[i].traverse(t)}traverseVisible(t){if(this.visible===!1)return;t(this);let e=this.children;for(let i=0,s=e.length;i<s;i++)e[i].traverseVisible(t)}traverseAncestors(t){let e=this.parent;e!==null&&(t(e),e.traverseAncestors(t))}updateMatrix(){this.matrix.compose(this.position,this.quaternion,this.scale),this.matrixWorldNeedsUpdate=!0}updateMatrixWorld(t){this.matrixAutoUpdate&&this.updateMatrix(),(this.matrixWorldNeedsUpdate||t)&&(this.matrixWorldAutoUpdate===!0&&(this.parent===null?this.matrixWorld.copy(this.matrix):this.matrixWorld.multiplyMatrices(this.parent.matrixWorld,this.matrix)),this.matrixWorldNeedsUpdate=!1,t=!0);let e=this.children;for(let i=0,s=e.length;i<s;i++)e[i].updateMatrixWorld(t)}updateWorldMatrix(t,e){let i=this.parent;if(t===!0&&i!==null&&i.updateWorldMatrix(!0,!1),this.matrixAutoUpdate&&this.updateMatrix(),this.matrixWorldAutoUpdate===!0&&(this.parent===null?this.matrixWorld.copy(this.matrix):this.matrixWorld.multiplyMatrices(this.parent.matrixWorld,this.matrix)),e===!0){let s=this.children;for(let r=0,o=s.length;r<o;r++)s[r].updateWorldMatrix(!1,!0)}}toJSON(t){let e=t===void 0||typeof t=="string",i={};e&&(t={geometries:{},materials:{},textures:{},images:{},shapes:{},skeletons:{},animations:{},nodes:{}},i.metadata={version:4.6,type:"Object",generator:"Object3D.toJSON"});let s={};s.uuid=this.uuid,s.type=this.type,this.name!==""&&(s.name=this.name),this.castShadow===!0&&(s.castShadow=!0),this.receiveShadow===!0&&(s.receiveShadow=!0),this.visible===!1&&(s.visible=!1),this.frustumCulled===!1&&(s.frustumCulled=!1),this.renderOrder!==0&&(s.renderOrder=this.renderOrder),Object.keys(this.userData).length>0&&(s.userData=this.userData),s.layers=this.layers.mask,s.matrix=this.matrix.toArray(),s.up=this.up.toArray(),this.matrixAutoUpdate===!1&&(s.matrixAutoUpdate=!1),this.isInstancedMesh&&(s.type="InstancedMesh",s.count=this.count,s.instanceMatrix=this.instanceMatrix.toJSON(),this.instanceColor!==null&&(s.instanceColor=this.instanceColor.toJSON())),this.isBatchedMesh&&(s.type="BatchedMesh",s.perObjectFrustumCulled=this.perObjectFrustumCulled,s.sortObjects=this.sortObjects,s.drawRanges=this._drawRanges,s.reservedRanges=this._reservedRanges,s.visibility=this._visibility,s.active=this._active,s.bounds=this._bounds.map(a=>({boxInitialized:a.boxInitialized,boxMin:a.box.min.toArray(),boxMax:a.box.max.toArray(),sphereInitialized:a.sphereInitialized,sphereRadius:a.sphere.radius,sphereCenter:a.sphere.center.toArray()})),s.maxInstanceCount=this._maxInstanceCount,s.maxVertexCount=this._maxVertexCount,s.maxIndexCount=this._maxIndexCount,s.geometryInitialized=this._geometryInitialized,s.geometryCount=this._geometryCount,s.matricesTexture=this._matricesTexture.toJSON(t),this._colorsTexture!==null&&(s.colorsTexture=this._colorsTexture.toJSON(t)),this.boundingSphere!==null&&(s.boundingSphere={center:s.boundingSphere.center.toArray(),radius:s.boundingSphere.radius}),this.boundingBox!==null&&(s.boundingBox={min:s.boundingBox.min.toArray(),max:s.boundingBox.max.toArray()}));function r(a,l){return a[l.uuid]===void 0&&(a[l.uuid]=l.toJSON(t)),l.uuid}if(this.isScene)this.background&&(this.background.isColor?s.background=this.background.toJSON():this.background.isTexture&&(s.background=this.background.toJSON(t).uuid)),this.environment&&this.environment.isTexture&&this.environment.isRenderTargetTexture!==!0&&(s.environment=this.environment.toJSON(t).uuid);else if(this.isMesh||this.isLine||this.isPoints){s.geometry=r(t.geometries,this.geometry);let a=this.geometry.parameters;if(a!==void 0&&a.shapes!==void 0){let l=a.shapes;if(Array.isArray(l))for(let c=0,h=l.length;c<h;c++){let d=l[c];r(t.shapes,d)}else r(t.shapes,l)}}if(this.isSkinnedMesh&&(s.bindMode=this.bindMode,s.bindMatrix=this.bindMatrix.toArray(),this.skeleton!==void 0&&(r(t.skeletons,this.skeleton),s.skeleton=this.skeleton.uuid)),this.material!==void 0)if(Array.isArray(this.material)){let a=[];for(let l=0,c=this.material.length;l<c;l++)a.push(r(t.materials,this.material[l]));s.material=a}else s.material=r(t.materials,this.material);if(this.children.length>0){s.children=[];for(let a=0;a<this.children.length;a++)s.children.push(this.children[a].toJSON(t).object)}if(this.animations.length>0){s.animations=[];for(let a=0;a<this.animations.length;a++){let l=this.animations[a];s.animations.push(r(t.animations,l))}}if(e){let a=o(t.geometries),l=o(t.materials),c=o(t.textures),h=o(t.images),d=o(t.shapes),p=o(t.skeletons),m=o(t.animations),g=o(t.nodes);a.length>0&&(i.geometries=a),l.length>0&&(i.materials=l),c.length>0&&(i.textures=c),h.length>0&&(i.images=h),d.length>0&&(i.shapes=d),p.length>0&&(i.skeletons=p),m.length>0&&(i.animations=m),g.length>0&&(i.nodes=g)}return i.object=s,i;function o(a){let l=[];for(let c in a){let h=a[c];delete h.metadata,l.push(h)}return l}}clone(t){return new this.constructor().copy(this,t)}copy(t,e=!0){if(this.name=t.name,this.up.copy(t.up),this.position.copy(t.position),this.rotation.order=t.rotation.order,this.quaternion.copy(t.quaternion),this.scale.copy(t.scale),this.matrix.copy(t.matrix),this.matrixWorld.copy(t.matrixWorld),this.matrixAutoUpdate=t.matrixAutoUpdate,this.matrixWorldAutoUpdate=t.matrixWorldAutoUpdate,this.matrixWorldNeedsUpdate=t.matrixWorldNeedsUpdate,this.layers.mask=t.layers.mask,this.visible=t.visible,this.castShadow=t.castShadow,this.receiveShadow=t.receiveShadow,this.frustumCulled=t.frustumCulled,this.renderOrder=t.renderOrder,this.animations=t.animations.slice(),this.userData=JSON.parse(JSON.stringify(t.userData)),e===!0)for(let i=0;i<t.children.length;i++){let s=t.children[i];this.add(s.clone())}return this}};Oe.DEFAULT_UP=new F(0,1,0);Oe.DEFAULT_MATRIX_AUTO_UPDATE=!0;Oe.DEFAULT_MATRIX_WORLD_AUTO_UPDATE=!0;var Ze=new F,xn=new F,za=new F,_n=new F,Zi=new F,Ji=new F,Qu=new F,ka=new F,Ha=new F,Ga=new F,Wa=new ae,Xa=new ae,qa=new ae,hi=class n{constructor(t=new F,e=new F,i=new F){this.a=t,this.b=e,this.c=i}static getNormal(t,e,i,s){s.subVectors(i,e),Ze.subVectors(t,e),s.cross(Ze);let r=s.lengthSq();return r>0?s.multiplyScalar(1/Math.sqrt(r)):s.set(0,0,0)}static getBarycoord(t,e,i,s,r){Ze.subVectors(s,e),xn.subVectors(i,e),za.subVectors(t,e);let o=Ze.dot(Ze),a=Ze.dot(xn),l=Ze.dot(za),c=xn.dot(xn),h=xn.dot(za),d=o*c-a*a;if(d===0)return r.set(0,0,0),null;let p=1/d,m=(c*l-a*h)*p,g=(o*h-a*l)*p;return r.set(1-m-g,g,m)}static containsPoint(t,e,i,s){return this.getBarycoord(t,e,i,s,_n)===null?!1:_n.x>=0&&_n.y>=0&&_n.x+_n.y<=1}static getInterpolation(t,e,i,s,r,o,a,l){return this.getBarycoord(t,e,i,s,_n)===null?(l.x=0,l.y=0,"z"in l&&(l.z=0),"w"in l&&(l.w=0),null):(l.setScalar(0),l.addScaledVector(r,_n.x),l.addScaledVector(o,_n.y),l.addScaledVector(a,_n.z),l)}static getInterpolatedAttribute(t,e,i,s,r,o){return Wa.setScalar(0),Xa.setScalar(0),qa.setScalar(0),Wa.fromBufferAttribute(t,e),Xa.fromBufferAttribute(t,i),qa.fromBufferAttribute(t,s),o.setScalar(0),o.addScaledVector(Wa,r.x),o.addScaledVector(Xa,r.y),o.addScaledVector(qa,r.z),o}static isFrontFacing(t,e,i,s){return Ze.subVectors(i,e),xn.subVectors(t,e),Ze.cross(xn).dot(s)<0}set(t,e,i){return this.a.copy(t),this.b.copy(e),this.c.copy(i),this}setFromPointsAndIndices(t,e,i,s){return this.a.copy(t[e]),this.b.copy(t[i]),this.c.copy(t[s]),this}setFromAttributeAndIndices(t,e,i,s){return this.a.fromBufferAttribute(t,e),this.b.fromBufferAttribute(t,i),this.c.fromBufferAttribute(t,s),this}clone(){return new this.constructor().copy(this)}copy(t){return this.a.copy(t.a),this.b.copy(t.b),this.c.copy(t.c),this}getArea(){return Ze.subVectors(this.c,this.b),xn.subVectors(this.a,this.b),Ze.cross(xn).length()*.5}getMidpoint(t){return t.addVectors(this.a,this.b).add(this.c).multiplyScalar(1/3)}getNormal(t){return n.getNormal(this.a,this.b,this.c,t)}getPlane(t){return t.setFromCoplanarPoints(this.a,this.b,this.c)}getBarycoord(t,e){return n.getBarycoord(t,this.a,this.b,this.c,e)}getInterpolation(t,e,i,s,r){return n.getInterpolation(t,this.a,this.b,this.c,e,i,s,r)}containsPoint(t){return n.containsPoint(t,this.a,this.b,this.c)}isFrontFacing(t){return n.isFrontFacing(this.a,this.b,this.c,t)}intersectsBox(t){return t.intersectsTriangle(this)}closestPointToPoint(t,e){let i=this.a,s=this.b,r=this.c,o,a;Zi.subVectors(s,i),Ji.subVectors(r,i),ka.subVectors(t,i);let l=Zi.dot(ka),c=Ji.dot(ka);if(l<=0&&c<=0)return e.copy(i);Ha.subVectors(t,s);let h=Zi.dot(Ha),d=Ji.dot(Ha);if(h>=0&&d<=h)return e.copy(s);let p=l*d-h*c;if(p<=0&&l>=0&&h<=0)return o=l/(l-h),e.copy(i).addScaledVector(Zi,o);Ga.subVectors(t,r);let m=Zi.dot(Ga),g=Ji.dot(Ga);if(g>=0&&m<=g)return e.copy(r);let _=m*c-l*g;if(_<=0&&c>=0&&g<=0)return a=c/(c-g),e.copy(i).addScaledVector(Ji,a);let u=h*g-m*d;if(u<=0&&d-h>=0&&m-g>=0)return Qu.subVectors(r,s),a=(d-h)/(d-h+(m-g)),e.copy(s).addScaledVector(Qu,a);let f=1/(u+_+p);return o=_*f,a=p*f,e.copy(i).addScaledVector(Zi,o).addScaledVector(Ji,a)}equals(t){return t.a.equals(this.a)&&t.b.equals(this.b)&&t.c.equals(this.c)}},Zf={aliceblue:15792383,antiquewhite:16444375,aqua:65535,aquamarine:8388564,azure:15794175,beige:16119260,bisque:16770244,black:0,blanchedalmond:16772045,blue:255,blueviolet:9055202,brown:10824234,burlywood:14596231,cadetblue:6266528,chartreuse:8388352,chocolate:13789470,coral:16744272,cornflowerblue:6591981,cornsilk:16775388,crimson:14423100,cyan:65535,darkblue:139,darkcyan:35723,darkgoldenrod:12092939,darkgray:11119017,darkgreen:25600,darkgrey:11119017,darkkhaki:12433259,darkmagenta:9109643,darkolivegreen:5597999,darkorange:16747520,darkorchid:10040012,darkred:9109504,darksalmon:15308410,darkseagreen:9419919,darkslateblue:4734347,darkslategray:3100495,darkslategrey:3100495,darkturquoise:52945,darkviolet:9699539,deeppink:16716947,deepskyblue:49151,dimgray:6908265,dimgrey:6908265,dodgerblue:2003199,firebrick:11674146,floralwhite:16775920,forestgreen:2263842,fuchsia:16711935,gainsboro:14474460,ghostwhite:16316671,gold:16766720,goldenrod:14329120,gray:8421504,green:32768,greenyellow:11403055,grey:8421504,honeydew:15794160,hotpink:16738740,indianred:13458524,indigo:4915330,ivory:16777200,khaki:15787660,lavender:15132410,lavenderblush:16773365,lawngreen:8190976,lemonchiffon:16775885,lightblue:11393254,lightcoral:15761536,lightcyan:14745599,lightgoldenrodyellow:16448210,lightgray:13882323,lightgreen:9498256,lightgrey:13882323,lightpink:16758465,lightsalmon:16752762,lightseagreen:2142890,lightskyblue:8900346,lightslategray:7833753,lightslategrey:7833753,lightsteelblue:11584734,lightyellow:16777184,lime:65280,limegreen:3329330,linen:16445670,magenta:16711935,maroon:8388608,mediumaquamarine:6737322,mediumblue:205,mediumorchid:12211667,mediumpurple:9662683,mediumseagreen:3978097,mediumslateblue:8087790,mediumspringgreen:64154,mediumturquoise:4772300,mediumvioletred:13047173,midnightblue:1644912,mintcream:16121850,mistyrose:16770273,moccasin:16770229,navajowhite:16768685,navy:128,oldlace:16643558,olive:8421376,olivedrab:7048739,orange:16753920,orangered:16729344,orchid:14315734,palegoldenrod:15657130,palegreen:10025880,paleturquoise:11529966,palevioletred:14381203,papayawhip:16773077,peachpuff:16767673,peru:13468991,pink:16761035,plum:14524637,powderblue:11591910,purple:8388736,rebeccapurple:6697881,red:16711680,rosybrown:12357519,royalblue:4286945,saddlebrown:9127187,salmon:16416882,sandybrown:16032864,seagreen:3050327,seashell:16774638,sienna:10506797,silver:12632256,skyblue:8900331,slateblue:6970061,slategray:7372944,slategrey:7372944,snow:16775930,springgreen:65407,steelblue:4620980,tan:13808780,teal:32896,thistle:14204888,tomato:16737095,turquoise:4251856,violet:15631086,wheat:16113331,white:16777215,whitesmoke:16119285,yellow:16776960,yellowgreen:10145074},zn={h:0,s:0,l:0},Zr={h:0,s:0,l:0};function Ya(n,t,e){return e<0&&(e+=1),e>1&&(e-=1),e<1/6?n+(t-n)*6*e:e<1/2?t:e<2/3?n+(t-n)*6*(2/3-e):n}var Gt=class{constructor(t,e,i){return this.isColor=!0,this.r=1,this.g=1,this.b=1,this.set(t,e,i)}set(t,e,i){if(e===void 0&&i===void 0){let s=t;s&&s.isColor?this.copy(s):typeof s=="number"?this.setHex(s):typeof s=="string"&&this.setStyle(s)}else this.setRGB(t,e,i);return this}setScalar(t){return this.r=t,this.g=t,this.b=t,this}setHex(t,e=an){return t=Math.floor(t),this.r=(t>>16&255)/255,this.g=(t>>8&255)/255,this.b=(t&255)/255,Yt.toWorkingColorSpace(this,e),this}setRGB(t,e,i,s=Yt.workingColorSpace){return this.r=t,this.g=e,this.b=i,Yt.toWorkingColorSpace(this,s),this}setHSL(t,e,i,s=Yt.workingColorSpace){if(t=bm(t,1),e=Ce(e,0,1),i=Ce(i,0,1),e===0)this.r=this.g=this.b=i;else{let r=i<=.5?i*(1+e):i+e-i*e,o=2*i-r;this.r=Ya(o,r,t+1/3),this.g=Ya(o,r,t),this.b=Ya(o,r,t-1/3)}return Yt.toWorkingColorSpace(this,s),this}setStyle(t,e=an){function i(r){r!==void 0&&parseFloat(r)<1&&console.warn("THREE.Color: Alpha component of "+t+" will be ignored.")}let s;if(s=/^(\w+)\(([^\)]*)\)/.exec(t)){let r,o=s[1],a=s[2];switch(o){case"rgb":case"rgba":if(r=/^\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*(?:,\s*(\d*\.?\d+)\s*)?$/.exec(a))return i(r[4]),this.setRGB(Math.min(255,parseInt(r[1],10))/255,Math.min(255,parseInt(r[2],10))/255,Math.min(255,parseInt(r[3],10))/255,e);if(r=/^\s*(\d+)\%\s*,\s*(\d+)\%\s*,\s*(\d+)\%\s*(?:,\s*(\d*\.?\d+)\s*)?$/.exec(a))return i(r[4]),this.setRGB(Math.min(100,parseInt(r[1],10))/100,Math.min(100,parseInt(r[2],10))/100,Math.min(100,parseInt(r[3],10))/100,e);break;case"hsl":case"hsla":if(r=/^\s*(\d*\.?\d+)\s*,\s*(\d*\.?\d+)\%\s*,\s*(\d*\.?\d+)\%\s*(?:,\s*(\d*\.?\d+)\s*)?$/.exec(a))return i(r[4]),this.setHSL(parseFloat(r[1])/360,parseFloat(r[2])/100,parseFloat(r[3])/100,e);break;default:console.warn("THREE.Color: Unknown color model "+t)}}else if(s=/^\#([A-Fa-f\d]+)$/.exec(t)){let r=s[1],o=r.length;if(o===3)return this.setRGB(parseInt(r.charAt(0),16)/15,parseInt(r.charAt(1),16)/15,parseInt(r.charAt(2),16)/15,e);if(o===6)return this.setHex(parseInt(r,16),e);console.warn("THREE.Color: Invalid hex color "+t)}else if(t&&t.length>0)return this.setColorName(t,e);return this}setColorName(t,e=an){let i=Zf[t.toLowerCase()];return i!==void 0?this.setHex(i,e):console.warn("THREE.Color: Unknown color "+t),this}clone(){return new this.constructor(this.r,this.g,this.b)}copy(t){return this.r=t.r,this.g=t.g,this.b=t.b,this}copySRGBToLinear(t){return this.r=os(t.r),this.g=os(t.g),this.b=os(t.b),this}copyLinearToSRGB(t){return this.r=Ia(t.r),this.g=Ia(t.g),this.b=Ia(t.b),this}convertSRGBToLinear(){return this.copySRGBToLinear(this),this}convertLinearToSRGB(){return this.copyLinearToSRGB(this),this}getHex(t=an){return Yt.fromWorkingColorSpace(_e.copy(this),t),Math.round(Ce(_e.r*255,0,255))*65536+Math.round(Ce(_e.g*255,0,255))*256+Math.round(Ce(_e.b*255,0,255))}getHexString(t=an){return("000000"+this.getHex(t).toString(16)).slice(-6)}getHSL(t,e=Yt.workingColorSpace){Yt.fromWorkingColorSpace(_e.copy(this),e);let i=_e.r,s=_e.g,r=_e.b,o=Math.max(i,s,r),a=Math.min(i,s,r),l,c,h=(a+o)/2;if(a===o)l=0,c=0;else{let d=o-a;switch(c=h<=.5?d/(o+a):d/(2-o-a),o){case i:l=(s-r)/d+(s<r?6:0);break;case s:l=(r-i)/d+2;break;case r:l=(i-s)/d+4;break}l/=6}return t.h=l,t.s=c,t.l=h,t}getRGB(t,e=Yt.workingColorSpace){return Yt.fromWorkingColorSpace(_e.copy(this),e),t.r=_e.r,t.g=_e.g,t.b=_e.b,t}getStyle(t=an){Yt.fromWorkingColorSpace(_e.copy(this),t);let e=_e.r,i=_e.g,s=_e.b;return t!==an?`color(${t} ${e.toFixed(3)} ${i.toFixed(3)} ${s.toFixed(3)})`:`rgb(${Math.round(e*255)},${Math.round(i*255)},${Math.round(s*255)})`}offsetHSL(t,e,i){return this.getHSL(zn),this.setHSL(zn.h+t,zn.s+e,zn.l+i)}add(t){return this.r+=t.r,this.g+=t.g,this.b+=t.b,this}addColors(t,e){return this.r=t.r+e.r,this.g=t.g+e.g,this.b=t.b+e.b,this}addScalar(t){return this.r+=t,this.g+=t,this.b+=t,this}sub(t){return this.r=Math.max(0,this.r-t.r),this.g=Math.max(0,this.g-t.g),this.b=Math.max(0,this.b-t.b),this}multiply(t){return this.r*=t.r,this.g*=t.g,this.b*=t.b,this}multiplyScalar(t){return this.r*=t,this.g*=t,this.b*=t,this}lerp(t,e){return this.r+=(t.r-this.r)*e,this.g+=(t.g-this.g)*e,this.b+=(t.b-this.b)*e,this}lerpColors(t,e,i){return this.r=t.r+(e.r-t.r)*i,this.g=t.g+(e.g-t.g)*i,this.b=t.b+(e.b-t.b)*i,this}lerpHSL(t,e){this.getHSL(zn),t.getHSL(Zr);let i=Ra(zn.h,Zr.h,e),s=Ra(zn.s,Zr.s,e),r=Ra(zn.l,Zr.l,e);return this.setHSL(i,s,r),this}setFromVector3(t){return this.r=t.x,this.g=t.y,this.b=t.z,this}applyMatrix3(t){let e=this.r,i=this.g,s=this.b,r=t.elements;return this.r=r[0]*e+r[3]*i+r[6]*s,this.g=r[1]*e+r[4]*i+r[7]*s,this.b=r[2]*e+r[5]*i+r[8]*s,this}equals(t){return t.r===this.r&&t.g===this.g&&t.b===this.b}fromArray(t,e=0){return this.r=t[e],this.g=t[e+1],this.b=t[e+2],this}toArray(t=[],e=0){return t[e]=this.r,t[e+1]=this.g,t[e+2]=this.b,t}fromBufferAttribute(t,e){return this.r=t.getX(e),this.g=t.getY(e),this.b=t.getZ(e),this}toJSON(){return this.getHex()}*[Symbol.iterator](){yield this.r,yield this.g,yield this.b}},_e=new Gt;Gt.NAMES=Zf;var Bm=0,Kn=class extends qn{constructor(){super(),this.isMaterial=!0,Object.defineProperty(this,"id",{value:Bm++}),this.uuid=qs(),this.name="",this.type="Material",this.blending=ss,this.side=Xn,this.vertexColors=!1,this.opacity=1,this.transparent=!1,this.alphaHash=!1,this.blendSrc=sl,this.blendDst=rl,this.blendEquation=ci,this.blendSrcAlpha=null,this.blendDstAlpha=null,this.blendEquationAlpha=null,this.blendColor=new Gt(0,0,0),this.blendAlpha=0,this.depthFunc=ls,this.depthTest=!0,this.depthWrite=!0,this.stencilWriteMask=255,this.stencilFunc=Bu,this.stencilRef=0,this.stencilFuncMask=255,this.stencilFail=Hi,this.stencilZFail=Hi,this.stencilZPass=Hi,this.stencilWrite=!1,this.clippingPlanes=null,this.clipIntersection=!1,this.clipShadows=!1,this.shadowSide=null,this.colorWrite=!0,this.precision=null,this.polygonOffset=!1,this.polygonOffsetFactor=0,this.polygonOffsetUnits=0,this.dithering=!1,this.alphaToCoverage=!1,this.premultipliedAlpha=!1,this.forceSinglePass=!1,this.visible=!0,this.toneMapped=!0,this.userData={},this.version=0,this._alphaTest=0}get alphaTest(){return this._alphaTest}set alphaTest(t){this._alphaTest>0!=t>0&&this.version++,this._alphaTest=t}onBeforeRender(){}onBeforeCompile(){}customProgramCacheKey(){return this.onBeforeCompile.toString()}setValues(t){if(t!==void 0)for(let e in t){let i=t[e];if(i===void 0){console.warn(`THREE.Material: parameter '${e}' has value of undefined.`);continue}let s=this[e];if(s===void 0){console.warn(`THREE.Material: '${e}' is not a property of THREE.${this.type}.`);continue}s&&s.isColor?s.set(i):s&&s.isVector3&&i&&i.isVector3?s.copy(i):this[e]=i}}toJSON(t){let e=t===void 0||typeof t=="string";e&&(t={textures:{},images:{}});let i={metadata:{version:4.6,type:"Material",generator:"Material.toJSON"}};i.uuid=this.uuid,i.type=this.type,this.name!==""&&(i.name=this.name),this.color&&this.color.isColor&&(i.color=this.color.getHex()),this.roughness!==void 0&&(i.roughness=this.roughness),this.metalness!==void 0&&(i.metalness=this.metalness),this.sheen!==void 0&&(i.sheen=this.sheen),this.sheenColor&&this.sheenColor.isColor&&(i.sheenColor=this.sheenColor.getHex()),this.sheenRoughness!==void 0&&(i.sheenRoughness=this.sheenRoughness),this.emissive&&this.emissive.isColor&&(i.emissive=this.emissive.getHex()),this.emissiveIntensity!==void 0&&this.emissiveIntensity!==1&&(i.emissiveIntensity=this.emissiveIntensity),this.specular&&this.specular.isColor&&(i.specular=this.specular.getHex()),this.specularIntensity!==void 0&&(i.specularIntensity=this.specularIntensity),this.specularColor&&this.specularColor.isColor&&(i.specularColor=this.specularColor.getHex()),this.shininess!==void 0&&(i.shininess=this.shininess),this.clearcoat!==void 0&&(i.clearcoat=this.clearcoat),this.clearcoatRoughness!==void 0&&(i.clearcoatRoughness=this.clearcoatRoughness),this.clearcoatMap&&this.clearcoatMap.isTexture&&(i.clearcoatMap=this.clearcoatMap.toJSON(t).uuid),this.clearcoatRoughnessMap&&this.clearcoatRoughnessMap.isTexture&&(i.clearcoatRoughnessMap=this.clearcoatRoughnessMap.toJSON(t).uuid),this.clearcoatNormalMap&&this.clearcoatNormalMap.isTexture&&(i.clearcoatNormalMap=this.clearcoatNormalMap.toJSON(t).uuid,i.clearcoatNormalScale=this.clearcoatNormalScale.toArray()),this.dispersion!==void 0&&(i.dispersion=this.dispersion),this.iridescence!==void 0&&(i.iridescence=this.iridescence),this.iridescenceIOR!==void 0&&(i.iridescenceIOR=this.iridescenceIOR),this.iridescenceThicknessRange!==void 0&&(i.iridescenceThicknessRange=this.iridescenceThicknessRange),this.iridescenceMap&&this.iridescenceMap.isTexture&&(i.iridescenceMap=this.iridescenceMap.toJSON(t).uuid),this.iridescenceThicknessMap&&this.iridescenceThicknessMap.isTexture&&(i.iridescenceThicknessMap=this.iridescenceThicknessMap.toJSON(t).uuid),this.anisotropy!==void 0&&(i.anisotropy=this.anisotropy),this.anisotropyRotation!==void 0&&(i.anisotropyRotation=this.anisotropyRotation),this.anisotropyMap&&this.anisotropyMap.isTexture&&(i.anisotropyMap=this.anisotropyMap.toJSON(t).uuid),this.map&&this.map.isTexture&&(i.map=this.map.toJSON(t).uuid),this.matcap&&this.matcap.isTexture&&(i.matcap=this.matcap.toJSON(t).uuid),this.alphaMap&&this.alphaMap.isTexture&&(i.alphaMap=this.alphaMap.toJSON(t).uuid),this.lightMap&&this.lightMap.isTexture&&(i.lightMap=this.lightMap.toJSON(t).uuid,i.lightMapIntensity=this.lightMapIntensity),this.aoMap&&this.aoMap.isTexture&&(i.aoMap=this.aoMap.toJSON(t).uuid,i.aoMapIntensity=this.aoMapIntensity),this.bumpMap&&this.bumpMap.isTexture&&(i.bumpMap=this.bumpMap.toJSON(t).uuid,i.bumpScale=this.bumpScale),this.normalMap&&this.normalMap.isTexture&&(i.normalMap=this.normalMap.toJSON(t).uuid,i.normalMapType=this.normalMapType,i.normalScale=this.normalScale.toArray()),this.displacementMap&&this.displacementMap.isTexture&&(i.displacementMap=this.displacementMap.toJSON(t).uuid,i.displacementScale=this.displacementScale,i.displacementBias=this.displacementBias),this.roughnessMap&&this.roughnessMap.isTexture&&(i.roughnessMap=this.roughnessMap.toJSON(t).uuid),this.metalnessMap&&this.metalnessMap.isTexture&&(i.metalnessMap=this.metalnessMap.toJSON(t).uuid),this.emissiveMap&&this.emissiveMap.isTexture&&(i.emissiveMap=this.emissiveMap.toJSON(t).uuid),this.specularMap&&this.specularMap.isTexture&&(i.specularMap=this.specularMap.toJSON(t).uuid),this.specularIntensityMap&&this.specularIntensityMap.isTexture&&(i.specularIntensityMap=this.specularIntensityMap.toJSON(t).uuid),this.specularColorMap&&this.specularColorMap.isTexture&&(i.specularColorMap=this.specularColorMap.toJSON(t).uuid),this.envMap&&this.envMap.isTexture&&(i.envMap=this.envMap.toJSON(t).uuid,this.combine!==void 0&&(i.combine=this.combine)),this.envMapRotation!==void 0&&(i.envMapRotation=this.envMapRotation.toArray()),this.envMapIntensity!==void 0&&(i.envMapIntensity=this.envMapIntensity),this.reflectivity!==void 0&&(i.reflectivity=this.reflectivity),this.refractionRatio!==void 0&&(i.refractionRatio=this.refractionRatio),this.gradientMap&&this.gradientMap.isTexture&&(i.gradientMap=this.gradientMap.toJSON(t).uuid),this.transmission!==void 0&&(i.transmission=this.transmission),this.transmissionMap&&this.transmissionMap.isTexture&&(i.transmissionMap=this.transmissionMap.toJSON(t).uuid),this.thickness!==void 0&&(i.thickness=this.thickness),this.thicknessMap&&this.thicknessMap.isTexture&&(i.thicknessMap=this.thicknessMap.toJSON(t).uuid),this.attenuationDistance!==void 0&&this.attenuationDistance!==1/0&&(i.attenuationDistance=this.attenuationDistance),this.attenuationColor!==void 0&&(i.attenuationColor=this.attenuationColor.getHex()),this.size!==void 0&&(i.size=this.size),this.shadowSide!==null&&(i.shadowSide=this.shadowSide),this.sizeAttenuation!==void 0&&(i.sizeAttenuation=this.sizeAttenuation),this.blending!==ss&&(i.blending=this.blending),this.side!==Xn&&(i.side=this.side),this.vertexColors===!0&&(i.vertexColors=!0),this.opacity<1&&(i.opacity=this.opacity),this.transparent===!0&&(i.transparent=!0),this.blendSrc!==sl&&(i.blendSrc=this.blendSrc),this.blendDst!==rl&&(i.blendDst=this.blendDst),this.blendEquation!==ci&&(i.blendEquation=this.blendEquation),this.blendSrcAlpha!==null&&(i.blendSrcAlpha=this.blendSrcAlpha),this.blendDstAlpha!==null&&(i.blendDstAlpha=this.blendDstAlpha),this.blendEquationAlpha!==null&&(i.blendEquationAlpha=this.blendEquationAlpha),this.blendColor&&this.blendColor.isColor&&(i.blendColor=this.blendColor.getHex()),this.blendAlpha!==0&&(i.blendAlpha=this.blendAlpha),this.depthFunc!==ls&&(i.depthFunc=this.depthFunc),this.depthTest===!1&&(i.depthTest=this.depthTest),this.depthWrite===!1&&(i.depthWrite=this.depthWrite),this.colorWrite===!1&&(i.colorWrite=this.colorWrite),this.stencilWriteMask!==255&&(i.stencilWriteMask=this.stencilWriteMask),this.stencilFunc!==Bu&&(i.stencilFunc=this.stencilFunc),this.stencilRef!==0&&(i.stencilRef=this.stencilRef),this.stencilFuncMask!==255&&(i.stencilFuncMask=this.stencilFuncMask),this.stencilFail!==Hi&&(i.stencilFail=this.stencilFail),this.stencilZFail!==Hi&&(i.stencilZFail=this.stencilZFail),this.stencilZPass!==Hi&&(i.stencilZPass=this.stencilZPass),this.stencilWrite===!0&&(i.stencilWrite=this.stencilWrite),this.rotation!==void 0&&this.rotation!==0&&(i.rotation=this.rotation),this.polygonOffset===!0&&(i.polygonOffset=!0),this.polygonOffsetFactor!==0&&(i.polygonOffsetFactor=this.polygonOffsetFactor),this.polygonOffsetUnits!==0&&(i.polygonOffsetUnits=this.polygonOffsetUnits),this.linewidth!==void 0&&this.linewidth!==1&&(i.linewidth=this.linewidth),this.dashSize!==void 0&&(i.dashSize=this.dashSize),this.gapSize!==void 0&&(i.gapSize=this.gapSize),this.scale!==void 0&&(i.scale=this.scale),this.dithering===!0&&(i.dithering=!0),this.alphaTest>0&&(i.alphaTest=this.alphaTest),this.alphaHash===!0&&(i.alphaHash=!0),this.alphaToCoverage===!0&&(i.alphaToCoverage=!0),this.premultipliedAlpha===!0&&(i.premultipliedAlpha=!0),this.forceSinglePass===!0&&(i.forceSinglePass=!0),this.wireframe===!0&&(i.wireframe=!0),this.wireframeLinewidth>1&&(i.wireframeLinewidth=this.wireframeLinewidth),this.wireframeLinecap!=="round"&&(i.wireframeLinecap=this.wireframeLinecap),this.wireframeLinejoin!=="round"&&(i.wireframeLinejoin=this.wireframeLinejoin),this.flatShading===!0&&(i.flatShading=!0),this.visible===!1&&(i.visible=!1),this.toneMapped===!1&&(i.toneMapped=!1),this.fog===!1&&(i.fog=!1),Object.keys(this.userData).length>0&&(i.userData=this.userData);function s(r){let o=[];for(let a in r){let l=r[a];delete l.metadata,o.push(l)}return o}if(e){let r=s(t.textures),o=s(t.images);r.length>0&&(i.textures=r),o.length>0&&(i.images=o)}return i}clone(){return new this.constructor().copy(this)}copy(t){this.name=t.name,this.blending=t.blending,this.side=t.side,this.vertexColors=t.vertexColors,this.opacity=t.opacity,this.transparent=t.transparent,this.blendSrc=t.blendSrc,this.blendDst=t.blendDst,this.blendEquation=t.blendEquation,this.blendSrcAlpha=t.blendSrcAlpha,this.blendDstAlpha=t.blendDstAlpha,this.blendEquationAlpha=t.blendEquationAlpha,this.blendColor.copy(t.blendColor),this.blendAlpha=t.blendAlpha,this.depthFunc=t.depthFunc,this.depthTest=t.depthTest,this.depthWrite=t.depthWrite,this.stencilWriteMask=t.stencilWriteMask,this.stencilFunc=t.stencilFunc,this.stencilRef=t.stencilRef,this.stencilFuncMask=t.stencilFuncMask,this.stencilFail=t.stencilFail,this.stencilZFail=t.stencilZFail,this.stencilZPass=t.stencilZPass,this.stencilWrite=t.stencilWrite;let e=t.clippingPlanes,i=null;if(e!==null){let s=e.length;i=new Array(s);for(let r=0;r!==s;++r)i[r]=e[r].clone()}return this.clippingPlanes=i,this.clipIntersection=t.clipIntersection,this.clipShadows=t.clipShadows,this.shadowSide=t.shadowSide,this.colorWrite=t.colorWrite,this.precision=t.precision,this.polygonOffset=t.polygonOffset,this.polygonOffsetFactor=t.polygonOffsetFactor,this.polygonOffsetUnits=t.polygonOffsetUnits,this.dithering=t.dithering,this.alphaTest=t.alphaTest,this.alphaHash=t.alphaHash,this.alphaToCoverage=t.alphaToCoverage,this.premultipliedAlpha=t.premultipliedAlpha,this.forceSinglePass=t.forceSinglePass,this.visible=t.visible,this.toneMapped=t.toneMapped,this.userData=JSON.parse(JSON.stringify(t.userData)),this}dispose(){this.dispatchEvent({type:"dispose"})}set needsUpdate(t){t===!0&&this.version++}onBuild(){console.warn("Material: onBuild() has been removed.")}},ds=class extends Kn{constructor(t){super(),this.isMeshBasicMaterial=!0,this.type="MeshBasicMaterial",this.color=new Gt(16777215),this.map=null,this.lightMap=null,this.lightMapIntensity=1,this.aoMap=null,this.aoMapIntensity=1,this.specularMap=null,this.alphaMap=null,this.envMap=null,this.envMapRotation=new wn,this.combine=Ff,this.reflectivity=1,this.refractionRatio=.98,this.wireframe=!1,this.wireframeLinewidth=1,this.wireframeLinecap="round",this.wireframeLinejoin="round",this.fog=!0,this.setValues(t)}copy(t){return super.copy(t),this.color.copy(t.color),this.map=t.map,this.lightMap=t.lightMap,this.lightMapIntensity=t.lightMapIntensity,this.aoMap=t.aoMap,this.aoMapIntensity=t.aoMapIntensity,this.specularMap=t.specularMap,this.alphaMap=t.alphaMap,this.envMap=t.envMap,this.envMapRotation.copy(t.envMapRotation),this.combine=t.combine,this.reflectivity=t.reflectivity,this.refractionRatio=t.refractionRatio,this.wireframe=t.wireframe,this.wireframeLinewidth=t.wireframeLinewidth,this.wireframeLinecap=t.wireframeLinecap,this.wireframeLinejoin=t.wireframeLinejoin,this.fog=t.fog,this}};var ue=new F,Jr=new Zt,Se=class{constructor(t,e,i=!1){if(Array.isArray(t))throw new TypeError("THREE.BufferAttribute: array should be a Typed Array.");this.isBufferAttribute=!0,this.name="",this.array=t,this.itemSize=e,this.count=t!==void 0?t.length/e:0,this.normalized=i,this.usage=Vu,this.updateRanges=[],this.gpuType=Mn,this.version=0}onUploadCallback(){}set needsUpdate(t){t===!0&&this.version++}setUsage(t){return this.usage=t,this}addUpdateRange(t,e){this.updateRanges.push({start:t,count:e})}clearUpdateRanges(){this.updateRanges.length=0}copy(t){return this.name=t.name,this.array=new t.array.constructor(t.array),this.itemSize=t.itemSize,this.count=t.count,this.normalized=t.normalized,this.usage=t.usage,this.gpuType=t.gpuType,this}copyAt(t,e,i){t*=this.itemSize,i*=e.itemSize;for(let s=0,r=this.itemSize;s<r;s++)this.array[t+s]=e.array[i+s];return this}copyArray(t){return this.array.set(t),this}applyMatrix3(t){if(this.itemSize===2)for(let e=0,i=this.count;e<i;e++)Jr.fromBufferAttribute(this,e),Jr.applyMatrix3(t),this.setXY(e,Jr.x,Jr.y);else if(this.itemSize===3)for(let e=0,i=this.count;e<i;e++)ue.fromBufferAttribute(this,e),ue.applyMatrix3(t),this.setXYZ(e,ue.x,ue.y,ue.z);return this}applyMatrix4(t){for(let e=0,i=this.count;e<i;e++)ue.fromBufferAttribute(this,e),ue.applyMatrix4(t),this.setXYZ(e,ue.x,ue.y,ue.z);return this}applyNormalMatrix(t){for(let e=0,i=this.count;e<i;e++)ue.fromBufferAttribute(this,e),ue.applyNormalMatrix(t),this.setXYZ(e,ue.x,ue.y,ue.z);return this}transformDirection(t){for(let e=0,i=this.count;e<i;e++)ue.fromBufferAttribute(this,e),ue.transformDirection(t),this.setXYZ(e,ue.x,ue.y,ue.z);return this}set(t,e=0){return this.array.set(t,e),this}getComponent(t,e){let i=this.array[t*this.itemSize+e];return this.normalized&&(i=Is(i,this.array)),i}setComponent(t,e,i){return this.normalized&&(i=Ae(i,this.array)),this.array[t*this.itemSize+e]=i,this}getX(t){let e=this.array[t*this.itemSize];return this.normalized&&(e=Is(e,this.array)),e}setX(t,e){return this.normalized&&(e=Ae(e,this.array)),this.array[t*this.itemSize]=e,this}getY(t){let e=this.array[t*this.itemSize+1];return this.normalized&&(e=Is(e,this.array)),e}setY(t,e){return this.normalized&&(e=Ae(e,this.array)),this.array[t*this.itemSize+1]=e,this}getZ(t){let e=this.array[t*this.itemSize+2];return this.normalized&&(e=Is(e,this.array)),e}setZ(t,e){return this.normalized&&(e=Ae(e,this.array)),this.array[t*this.itemSize+2]=e,this}getW(t){let e=this.array[t*this.itemSize+3];return this.normalized&&(e=Is(e,this.array)),e}setW(t,e){return this.normalized&&(e=Ae(e,this.array)),this.array[t*this.itemSize+3]=e,this}setXY(t,e,i){return t*=this.itemSize,this.normalized&&(e=Ae(e,this.array),i=Ae(i,this.array)),this.array[t+0]=e,this.array[t+1]=i,this}setXYZ(t,e,i,s){return t*=this.itemSize,this.normalized&&(e=Ae(e,this.array),i=Ae(i,this.array),s=Ae(s,this.array)),this.array[t+0]=e,this.array[t+1]=i,this.array[t+2]=s,this}setXYZW(t,e,i,s,r){return t*=this.itemSize,this.normalized&&(e=Ae(e,this.array),i=Ae(i,this.array),s=Ae(s,this.array),r=Ae(r,this.array)),this.array[t+0]=e,this.array[t+1]=i,this.array[t+2]=s,this.array[t+3]=r,this}onUpload(t){return this.onUploadCallback=t,this}clone(){return new this.constructor(this.array,this.itemSize).copy(this)}toJSON(){let t={itemSize:this.itemSize,type:this.array.constructor.name,array:Array.from(this.array),normalized:this.normalized};return this.name!==""&&(t.name=this.name),this.usage!==Vu&&(t.usage=this.usage),t}};var Io=class extends Se{constructor(t,e,i){super(new Uint16Array(t),e,i)}};var Lo=class extends Se{constructor(t,e,i){super(new Uint32Array(t),e,i)}};var Pe=class extends Se{constructor(t,e,i){super(new Float32Array(t),e,i)}},Vm=0,Ge=new le,Ka=new Oe,Qi=new F,Ne=new mi,Fs=new mi,pe=new F,Be=class n extends qn{constructor(){super(),this.isBufferGeometry=!0,Object.defineProperty(this,"id",{value:Vm++}),this.uuid=qs(),this.name="",this.type="BufferGeometry",this.index=null,this.attributes={},this.morphAttributes={},this.morphTargetsRelative=!1,this.groups=[],this.boundingBox=null,this.boundingSphere=null,this.drawRange={start:0,count:1/0},this.userData={}}getIndex(){return this.index}setIndex(t){return Array.isArray(t)?this.index=new($f(t)?Lo:Io)(t,1):this.index=t,this}getAttribute(t){return this.attributes[t]}setAttribute(t,e){return this.attributes[t]=e,this}deleteAttribute(t){return delete this.attributes[t],this}hasAttribute(t){return this.attributes[t]!==void 0}addGroup(t,e,i=0){this.groups.push({start:t,count:e,materialIndex:i})}clearGroups(){this.groups=[]}setDrawRange(t,e){this.drawRange.start=t,this.drawRange.count=e}applyMatrix4(t){let e=this.attributes.position;e!==void 0&&(e.applyMatrix4(t),e.needsUpdate=!0);let i=this.attributes.normal;if(i!==void 0){let r=new Ft().getNormalMatrix(t);i.applyNormalMatrix(r),i.needsUpdate=!0}let s=this.attributes.tangent;return s!==void 0&&(s.transformDirection(t),s.needsUpdate=!0),this.boundingBox!==null&&this.computeBoundingBox(),this.boundingSphere!==null&&this.computeBoundingSphere(),this}applyQuaternion(t){return Ge.makeRotationFromQuaternion(t),this.applyMatrix4(Ge),this}rotateX(t){return Ge.makeRotationX(t),this.applyMatrix4(Ge),this}rotateY(t){return Ge.makeRotationY(t),this.applyMatrix4(Ge),this}rotateZ(t){return Ge.makeRotationZ(t),this.applyMatrix4(Ge),this}translate(t,e,i){return Ge.makeTranslation(t,e,i),this.applyMatrix4(Ge),this}scale(t,e,i){return Ge.makeScale(t,e,i),this.applyMatrix4(Ge),this}lookAt(t){return Ka.lookAt(t),Ka.updateMatrix(),this.applyMatrix4(Ka.matrix),this}center(){return this.computeBoundingBox(),this.boundingBox.getCenter(Qi).negate(),this.translate(Qi.x,Qi.y,Qi.z),this}setFromPoints(t){let e=[];for(let i=0,s=t.length;i<s;i++){let r=t[i];e.push(r.x,r.y,r.z||0)}return this.setAttribute("position",new Pe(e,3)),this}computeBoundingBox(){this.boundingBox===null&&(this.boundingBox=new mi);let t=this.attributes.position,e=this.morphAttributes.position;if(t&&t.isGLBufferAttribute){console.error("THREE.BufferGeometry.computeBoundingBox(): GLBufferAttribute requires a manual bounding box.",this),this.boundingBox.set(new F(-1/0,-1/0,-1/0),new F(1/0,1/0,1/0));return}if(t!==void 0){if(this.boundingBox.setFromBufferAttribute(t),e)for(let i=0,s=e.length;i<s;i++){let r=e[i];Ne.setFromBufferAttribute(r),this.morphTargetsRelative?(pe.addVectors(this.boundingBox.min,Ne.min),this.boundingBox.expandByPoint(pe),pe.addVectors(this.boundingBox.max,Ne.max),this.boundingBox.expandByPoint(pe)):(this.boundingBox.expandByPoint(Ne.min),this.boundingBox.expandByPoint(Ne.max))}}else this.boundingBox.makeEmpty();(isNaN(this.boundingBox.min.x)||isNaN(this.boundingBox.min.y)||isNaN(this.boundingBox.min.z))&&console.error('THREE.BufferGeometry.computeBoundingBox(): Computed min/max have NaN values. The "position" attribute is likely to have NaN values.',this)}computeBoundingSphere(){this.boundingSphere===null&&(this.boundingSphere=new gi);let t=this.attributes.position,e=this.morphAttributes.position;if(t&&t.isGLBufferAttribute){console.error("THREE.BufferGeometry.computeBoundingSphere(): GLBufferAttribute requires a manual bounding sphere.",this),this.boundingSphere.set(new F,1/0);return}if(t){let i=this.boundingSphere.center;if(Ne.setFromBufferAttribute(t),e)for(let r=0,o=e.length;r<o;r++){let a=e[r];Fs.setFromBufferAttribute(a),this.morphTargetsRelative?(pe.addVectors(Ne.min,Fs.min),Ne.expandByPoint(pe),pe.addVectors(Ne.max,Fs.max),Ne.expandByPoint(pe)):(Ne.expandByPoint(Fs.min),Ne.expandByPoint(Fs.max))}Ne.getCenter(i);let s=0;for(let r=0,o=t.count;r<o;r++)pe.fromBufferAttribute(t,r),s=Math.max(s,i.distanceToSquared(pe));if(e)for(let r=0,o=e.length;r<o;r++){let a=e[r],l=this.morphTargetsRelative;for(let c=0,h=a.count;c<h;c++)pe.fromBufferAttribute(a,c),l&&(Qi.fromBufferAttribute(t,c),pe.add(Qi)),s=Math.max(s,i.distanceToSquared(pe))}this.boundingSphere.radius=Math.sqrt(s),isNaN(this.boundingSphere.radius)&&console.error('THREE.BufferGeometry.computeBoundingSphere(): Computed radius is NaN. The "position" attribute is likely to have NaN values.',this)}}computeTangents(){let t=this.index,e=this.attributes;if(t===null||e.position===void 0||e.normal===void 0||e.uv===void 0){console.error("THREE.BufferGeometry: .computeTangents() failed. Missing required attributes (index, position, normal or uv)");return}let i=e.position,s=e.normal,r=e.uv;this.hasAttribute("tangent")===!1&&this.setAttribute("tangent",new Se(new Float32Array(4*i.count),4));let o=this.getAttribute("tangent"),a=[],l=[];for(let P=0;P<i.count;P++)a[P]=new F,l[P]=new F;let c=new F,h=new F,d=new F,p=new Zt,m=new Zt,g=new Zt,_=new F,u=new F;function f(P,G,x){c.fromBufferAttribute(i,P),h.fromBufferAttribute(i,G),d.fromBufferAttribute(i,x),p.fromBufferAttribute(r,P),m.fromBufferAttribute(r,G),g.fromBufferAttribute(r,x),h.sub(c),d.sub(c),m.sub(p),g.sub(p);let y=1/(m.x*g.y-g.x*m.y);isFinite(y)&&(_.copy(h).multiplyScalar(g.y).addScaledVector(d,-m.y).multiplyScalar(y),u.copy(d).multiplyScalar(m.x).addScaledVector(h,-g.x).multiplyScalar(y),a[P].add(_),a[G].add(_),a[x].add(_),l[P].add(u),l[G].add(u),l[x].add(u))}let b=this.groups;b.length===0&&(b=[{start:0,count:t.count}]);for(let P=0,G=b.length;P<G;++P){let x=b[P],y=x.start,V=x.count;for(let U=y,H=y+V;U<H;U+=3)f(t.getX(U+0),t.getX(U+1),t.getX(U+2))}let S=new F,E=new F,C=new F,A=new F;function T(P){C.fromBufferAttribute(s,P),A.copy(C);let G=a[P];S.copy(G),S.sub(C.multiplyScalar(C.dot(G))).normalize(),E.crossVectors(A,G);let y=E.dot(l[P])<0?-1:1;o.setXYZW(P,S.x,S.y,S.z,y)}for(let P=0,G=b.length;P<G;++P){let x=b[P],y=x.start,V=x.count;for(let U=y,H=y+V;U<H;U+=3)T(t.getX(U+0)),T(t.getX(U+1)),T(t.getX(U+2))}}computeVertexNormals(){let t=this.index,e=this.getAttribute("position");if(e!==void 0){let i=this.getAttribute("normal");if(i===void 0)i=new Se(new Float32Array(e.count*3),3),this.setAttribute("normal",i);else for(let p=0,m=i.count;p<m;p++)i.setXYZ(p,0,0,0);let s=new F,r=new F,o=new F,a=new F,l=new F,c=new F,h=new F,d=new F;if(t)for(let p=0,m=t.count;p<m;p+=3){let g=t.getX(p+0),_=t.getX(p+1),u=t.getX(p+2);s.fromBufferAttribute(e,g),r.fromBufferAttribute(e,_),o.fromBufferAttribute(e,u),h.subVectors(o,r),d.subVectors(s,r),h.cross(d),a.fromBufferAttribute(i,g),l.fromBufferAttribute(i,_),c.fromBufferAttribute(i,u),a.add(h),l.add(h),c.add(h),i.setXYZ(g,a.x,a.y,a.z),i.setXYZ(_,l.x,l.y,l.z),i.setXYZ(u,c.x,c.y,c.z)}else for(let p=0,m=e.count;p<m;p+=3)s.fromBufferAttribute(e,p+0),r.fromBufferAttribute(e,p+1),o.fromBufferAttribute(e,p+2),h.subVectors(o,r),d.subVectors(s,r),h.cross(d),i.setXYZ(p+0,h.x,h.y,h.z),i.setXYZ(p+1,h.x,h.y,h.z),i.setXYZ(p+2,h.x,h.y,h.z);this.normalizeNormals(),i.needsUpdate=!0}}normalizeNormals(){let t=this.attributes.normal;for(let e=0,i=t.count;e<i;e++)pe.fromBufferAttribute(t,e),pe.normalize(),t.setXYZ(e,pe.x,pe.y,pe.z)}toNonIndexed(){function t(a,l){let c=a.array,h=a.itemSize,d=a.normalized,p=new c.constructor(l.length*h),m=0,g=0;for(let _=0,u=l.length;_<u;_++){a.isInterleavedBufferAttribute?m=l[_]*a.data.stride+a.offset:m=l[_]*h;for(let f=0;f<h;f++)p[g++]=c[m++]}return new Se(p,h,d)}if(this.index===null)return console.warn("THREE.BufferGeometry.toNonIndexed(): BufferGeometry is already non-indexed."),this;let e=new n,i=this.index.array,s=this.attributes;for(let a in s){let l=s[a],c=t(l,i);e.setAttribute(a,c)}let r=this.morphAttributes;for(let a in r){let l=[],c=r[a];for(let h=0,d=c.length;h<d;h++){let p=c[h],m=t(p,i);l.push(m)}e.morphAttributes[a]=l}e.morphTargetsRelative=this.morphTargetsRelative;let o=this.groups;for(let a=0,l=o.length;a<l;a++){let c=o[a];e.addGroup(c.start,c.count,c.materialIndex)}return e}toJSON(){let t={metadata:{version:4.6,type:"BufferGeometry",generator:"BufferGeometry.toJSON"}};if(t.uuid=this.uuid,t.type=this.type,this.name!==""&&(t.name=this.name),Object.keys(this.userData).length>0&&(t.userData=this.userData),this.parameters!==void 0){let l=this.parameters;for(let c in l)l[c]!==void 0&&(t[c]=l[c]);return t}t.data={attributes:{}};let e=this.index;e!==null&&(t.data.index={type:e.array.constructor.name,array:Array.prototype.slice.call(e.array)});let i=this.attributes;for(let l in i){let c=i[l];t.data.attributes[l]=c.toJSON(t.data)}let s={},r=!1;for(let l in this.morphAttributes){let c=this.morphAttributes[l],h=[];for(let d=0,p=c.length;d<p;d++){let m=c[d];h.push(m.toJSON(t.data))}h.length>0&&(s[l]=h,r=!0)}r&&(t.data.morphAttributes=s,t.data.morphTargetsRelative=this.morphTargetsRelative);let o=this.groups;o.length>0&&(t.data.groups=JSON.parse(JSON.stringify(o)));let a=this.boundingSphere;return a!==null&&(t.data.boundingSphere={center:a.center.toArray(),radius:a.radius}),t}clone(){return new this.constructor().copy(this)}copy(t){this.index=null,this.attributes={},this.morphAttributes={},this.groups=[],this.boundingBox=null,this.boundingSphere=null;let e={};this.name=t.name;let i=t.index;i!==null&&this.setIndex(i.clone(e));let s=t.attributes;for(let c in s){let h=s[c];this.setAttribute(c,h.clone(e))}let r=t.morphAttributes;for(let c in r){let h=[],d=r[c];for(let p=0,m=d.length;p<m;p++)h.push(d[p].clone(e));this.morphAttributes[c]=h}this.morphTargetsRelative=t.morphTargetsRelative;let o=t.groups;for(let c=0,h=o.length;c<h;c++){let d=o[c];this.addGroup(d.start,d.count,d.materialIndex)}let a=t.boundingBox;a!==null&&(this.boundingBox=a.clone());let l=t.boundingSphere;return l!==null&&(this.boundingSphere=l.clone()),this.drawRange.start=t.drawRange.start,this.drawRange.count=t.drawRange.count,this.userData=t.userData,this}dispose(){this.dispatchEvent({type:"dispose"})}},ju=new le,si=new ks,Qr=new gi,tf=new F,jr=new F,to=new F,eo=new F,$a=new F,no=new F,ef=new F,io=new F,Fe=class extends Oe{constructor(t=new Be,e=new ds){super(),this.isMesh=!0,this.type="Mesh",this.geometry=t,this.material=e,this.updateMorphTargets()}copy(t,e){return super.copy(t,e),t.morphTargetInfluences!==void 0&&(this.morphTargetInfluences=t.morphTargetInfluences.slice()),t.morphTargetDictionary!==void 0&&(this.morphTargetDictionary=Object.assign({},t.morphTargetDictionary)),this.material=Array.isArray(t.material)?t.material.slice():t.material,this.geometry=t.geometry,this}updateMorphTargets(){let e=this.geometry.morphAttributes,i=Object.keys(e);if(i.length>0){let s=e[i[0]];if(s!==void 0){this.morphTargetInfluences=[],this.morphTargetDictionary={};for(let r=0,o=s.length;r<o;r++){let a=s[r].name||String(r);this.morphTargetInfluences.push(0),this.morphTargetDictionary[a]=r}}}}getVertexPosition(t,e){let i=this.geometry,s=i.attributes.position,r=i.morphAttributes.position,o=i.morphTargetsRelative;e.fromBufferAttribute(s,t);let a=this.morphTargetInfluences;if(r&&a){no.set(0,0,0);for(let l=0,c=r.length;l<c;l++){let h=a[l],d=r[l];h!==0&&($a.fromBufferAttribute(d,t),o?no.addScaledVector($a,h):no.addScaledVector($a.sub(e),h))}e.add(no)}return e}raycast(t,e){let i=this.geometry,s=this.material,r=this.matrixWorld;s!==void 0&&(i.boundingSphere===null&&i.computeBoundingSphere(),Qr.copy(i.boundingSphere),Qr.applyMatrix4(r),si.copy(t.ray).recast(t.near),!(Qr.containsPoint(si.origin)===!1&&(si.intersectSphere(Qr,tf)===null||si.origin.distanceToSquared(tf)>(t.far-t.near)**2))&&(ju.copy(r).invert(),si.copy(t.ray).applyMatrix4(ju),!(i.boundingBox!==null&&si.intersectsBox(i.boundingBox)===!1)&&this._computeIntersections(t,e,si)))}_computeIntersections(t,e,i){let s,r=this.geometry,o=this.material,a=r.index,l=r.attributes.position,c=r.attributes.uv,h=r.attributes.uv1,d=r.attributes.normal,p=r.groups,m=r.drawRange;if(a!==null)if(Array.isArray(o))for(let g=0,_=p.length;g<_;g++){let u=p[g],f=o[u.materialIndex],b=Math.max(u.start,m.start),S=Math.min(a.count,Math.min(u.start+u.count,m.start+m.count));for(let E=b,C=S;E<C;E+=3){let A=a.getX(E),T=a.getX(E+1),P=a.getX(E+2);s=so(this,f,t,i,c,h,d,A,T,P),s&&(s.faceIndex=Math.floor(E/3),s.face.materialIndex=u.materialIndex,e.push(s))}}else{let g=Math.max(0,m.start),_=Math.min(a.count,m.start+m.count);for(let u=g,f=_;u<f;u+=3){let b=a.getX(u),S=a.getX(u+1),E=a.getX(u+2);s=so(this,o,t,i,c,h,d,b,S,E),s&&(s.faceIndex=Math.floor(u/3),e.push(s))}}else if(l!==void 0)if(Array.isArray(o))for(let g=0,_=p.length;g<_;g++){let u=p[g],f=o[u.materialIndex],b=Math.max(u.start,m.start),S=Math.min(l.count,Math.min(u.start+u.count,m.start+m.count));for(let E=b,C=S;E<C;E+=3){let A=E,T=E+1,P=E+2;s=so(this,f,t,i,c,h,d,A,T,P),s&&(s.faceIndex=Math.floor(E/3),s.face.materialIndex=u.materialIndex,e.push(s))}}else{let g=Math.max(0,m.start),_=Math.min(l.count,m.start+m.count);for(let u=g,f=_;u<f;u+=3){let b=u,S=u+1,E=u+2;s=so(this,o,t,i,c,h,d,b,S,E),s&&(s.faceIndex=Math.floor(u/3),e.push(s))}}}};function zm(n,t,e,i,s,r,o,a){let l;if(t.side===Re?l=i.intersectTriangle(o,r,s,!0,a):l=i.intersectTriangle(s,r,o,t.side===Xn,a),l===null)return null;io.copy(a),io.applyMatrix4(n.matrixWorld);let c=e.ray.origin.distanceTo(io);return c<e.near||c>e.far?null:{distance:c,point:io.clone(),object:n}}function so(n,t,e,i,s,r,o,a,l,c){n.getVertexPosition(a,jr),n.getVertexPosition(l,to),n.getVertexPosition(c,eo);let h=zm(n,t,e,i,jr,to,eo,ef);if(h){let d=new F;hi.getBarycoord(ef,jr,to,eo,d),s&&(h.uv=hi.getInterpolatedAttribute(s,a,l,c,d,new Zt)),r&&(h.uv1=hi.getInterpolatedAttribute(r,a,l,c,d,new Zt)),o&&(h.normal=hi.getInterpolatedAttribute(o,a,l,c,d,new F),h.normal.dot(i.direction)>0&&h.normal.multiplyScalar(-1));let p={a,b:l,c,normal:new F,materialIndex:0};hi.getNormal(jr,to,eo,p.normal),h.face=p,h.barycoord=d}return h}var Hs=class n extends Be{constructor(t=1,e=1,i=1,s=1,r=1,o=1){super(),this.type="BoxGeometry",this.parameters={width:t,height:e,depth:i,widthSegments:s,heightSegments:r,depthSegments:o};let a=this;s=Math.floor(s),r=Math.floor(r),o=Math.floor(o);let l=[],c=[],h=[],d=[],p=0,m=0;g("z","y","x",-1,-1,i,e,t,o,r,0),g("z","y","x",1,-1,i,e,-t,o,r,1),g("x","z","y",1,1,t,i,e,s,o,2),g("x","z","y",1,-1,t,i,-e,s,o,3),g("x","y","z",1,-1,t,e,i,s,r,4),g("x","y","z",-1,-1,t,e,-i,s,r,5),this.setIndex(l),this.setAttribute("position",new Pe(c,3)),this.setAttribute("normal",new Pe(h,3)),this.setAttribute("uv",new Pe(d,2));function g(_,u,f,b,S,E,C,A,T,P,G){let x=E/T,y=C/P,V=E/2,U=C/2,H=A/2,$=T+1,k=P+1,Z=0,z=0,it=new F;for(let ot=0;ot<k;ot++){let pt=ot*y-U;for(let Bt=0;Bt<$;Bt++){let Vt=Bt*x-V;it[_]=Vt*b,it[u]=pt*S,it[f]=H,c.push(it.x,it.y,it.z),it[_]=0,it[u]=0,it[f]=A>0?1:-1,h.push(it.x,it.y,it.z),d.push(Bt/T),d.push(1-ot/P),Z+=1}}for(let ot=0;ot<P;ot++)for(let pt=0;pt<T;pt++){let Bt=p+pt+$*ot,Vt=p+pt+$*(ot+1),X=p+(pt+1)+$*(ot+1),J=p+(pt+1)+$*ot;l.push(Bt,Vt,J),l.push(Vt,X,J),z+=6}a.addGroup(m,z,G),m+=z,p+=Z}}copy(t){return super.copy(t),this.parameters=Object.assign({},t.parameters),this}static fromJSON(t){return new n(t.width,t.height,t.depth,t.widthSegments,t.heightSegments,t.depthSegments)}};function ps(n){let t={};for(let e in n){t[e]={};for(let i in n[e]){let s=n[e][i];s&&(s.isColor||s.isMatrix3||s.isMatrix4||s.isVector2||s.isVector3||s.isVector4||s.isTexture||s.isQuaternion)?s.isRenderTargetTexture?(console.warn("UniformsUtils: Textures of render targets cannot be cloned via cloneUniforms() or mergeUniforms()."),t[e][i]=null):t[e][i]=s.clone():Array.isArray(s)?t[e][i]=s.slice():t[e][i]=s}}return t}function ye(n){let t={};for(let e=0;e<n.length;e++){let i=ps(n[e]);for(let s in i)t[s]=i[s]}return t}function km(n){let t=[];for(let e=0;e<n.length;e++)t.push(n[e].clone());return t}function Jf(n){let t=n.getRenderTarget();return t===null?n.outputColorSpace:t.isXRRenderTarget===!0?t.texture.colorSpace:Yt.workingColorSpace}var Hm={clone:ps,merge:ye},Gm=`void main() {
	gl_Position = projectionMatrix * modelViewMatrix * vec4( position, 1.0 );
}`,Wm=`void main() {
	gl_FragColor = vec4( 1.0, 0.0, 0.0, 1.0 );
}`,cn=class extends Kn{constructor(t){super(),this.isShaderMaterial=!0,this.type="ShaderMaterial",this.defines={},this.uniforms={},this.uniformsGroups=[],this.vertexShader=Gm,this.fragmentShader=Wm,this.linewidth=1,this.wireframe=!1,this.wireframeLinewidth=1,this.fog=!1,this.lights=!1,this.clipping=!1,this.forceSinglePass=!0,this.extensions={clipCullDistance:!1,multiDraw:!1},this.defaultAttributeValues={color:[1,1,1],uv:[0,0],uv1:[0,0]},this.index0AttributeName=void 0,this.uniformsNeedUpdate=!1,this.glslVersion=null,t!==void 0&&this.setValues(t)}copy(t){return super.copy(t),this.fragmentShader=t.fragmentShader,this.vertexShader=t.vertexShader,this.uniforms=ps(t.uniforms),this.uniformsGroups=km(t.uniformsGroups),this.defines=Object.assign({},t.defines),this.wireframe=t.wireframe,this.wireframeLinewidth=t.wireframeLinewidth,this.fog=t.fog,this.lights=t.lights,this.clipping=t.clipping,this.extensions=Object.assign({},t.extensions),this.glslVersion=t.glslVersion,this}toJSON(t){let e=super.toJSON(t);e.glslVersion=this.glslVersion,e.uniforms={};for(let s in this.uniforms){let o=this.uniforms[s].value;o&&o.isTexture?e.uniforms[s]={type:"t",value:o.toJSON(t).uuid}:o&&o.isColor?e.uniforms[s]={type:"c",value:o.getHex()}:o&&o.isVector2?e.uniforms[s]={type:"v2",value:o.toArray()}:o&&o.isVector3?e.uniforms[s]={type:"v3",value:o.toArray()}:o&&o.isVector4?e.uniforms[s]={type:"v4",value:o.toArray()}:o&&o.isMatrix3?e.uniforms[s]={type:"m3",value:o.toArray()}:o&&o.isMatrix4?e.uniforms[s]={type:"m4",value:o.toArray()}:e.uniforms[s]={value:o}}Object.keys(this.defines).length>0&&(e.defines=this.defines),e.vertexShader=this.vertexShader,e.fragmentShader=this.fragmentShader,e.lights=this.lights,e.clipping=this.clipping;let i={};for(let s in this.extensions)this.extensions[s]===!0&&(i[s]=!0);return Object.keys(i).length>0&&(e.extensions=i),e}},Do=class extends Oe{constructor(){super(),this.isCamera=!0,this.type="Camera",this.matrixWorldInverse=new le,this.projectionMatrix=new le,this.projectionMatrixInverse=new le,this.coordinateSystem=Sn}copy(t,e){return super.copy(t,e),this.matrixWorldInverse.copy(t.matrixWorldInverse),this.projectionMatrix.copy(t.projectionMatrix),this.projectionMatrixInverse.copy(t.projectionMatrixInverse),this.coordinateSystem=t.coordinateSystem,this}getWorldDirection(t){return super.getWorldDirection(t).negate()}updateMatrixWorld(t){super.updateMatrixWorld(t),this.matrixWorldInverse.copy(this.matrixWorld).invert()}updateWorldMatrix(t,e){super.updateWorldMatrix(t,e),this.matrixWorldInverse.copy(this.matrixWorld).invert()}clone(){return new this.constructor().copy(this)}},kn=new F,nf=new Zt,sf=new Zt,Me=class extends Do{constructor(t=50,e=1,i=.1,s=2e3){super(),this.isPerspectiveCamera=!0,this.type="PerspectiveCamera",this.fov=t,this.zoom=1,this.near=i,this.far=s,this.focus=10,this.aspect=e,this.view=null,this.filmGauge=35,this.filmOffset=0,this.updateProjectionMatrix()}copy(t,e){return super.copy(t,e),this.fov=t.fov,this.zoom=t.zoom,this.near=t.near,this.far=t.far,this.focus=t.focus,this.aspect=t.aspect,this.view=t.view===null?null:Object.assign({},t.view),this.filmGauge=t.filmGauge,this.filmOffset=t.filmOffset,this}setFocalLength(t){let e=.5*this.getFilmHeight()/t;this.fov=Wl*2*Math.atan(e),this.updateProjectionMatrix()}getFocalLength(){let t=Math.tan(Ca*.5*this.fov);return .5*this.getFilmHeight()/t}getEffectiveFOV(){return Wl*2*Math.atan(Math.tan(Ca*.5*this.fov)/this.zoom)}getFilmWidth(){return this.filmGauge*Math.min(this.aspect,1)}getFilmHeight(){return this.filmGauge/Math.max(this.aspect,1)}getViewBounds(t,e,i){kn.set(-1,-1,.5).applyMatrix4(this.projectionMatrixInverse),e.set(kn.x,kn.y).multiplyScalar(-t/kn.z),kn.set(1,1,.5).applyMatrix4(this.projectionMatrixInverse),i.set(kn.x,kn.y).multiplyScalar(-t/kn.z)}getViewSize(t,e){return this.getViewBounds(t,nf,sf),e.subVectors(sf,nf)}setViewOffset(t,e,i,s,r,o){this.aspect=t/e,this.view===null&&(this.view={enabled:!0,fullWidth:1,fullHeight:1,offsetX:0,offsetY:0,width:1,height:1}),this.view.enabled=!0,this.view.fullWidth=t,this.view.fullHeight=e,this.view.offsetX=i,this.view.offsetY=s,this.view.width=r,this.view.height=o,this.updateProjectionMatrix()}clearViewOffset(){this.view!==null&&(this.view.enabled=!1),this.updateProjectionMatrix()}updateProjectionMatrix(){let t=this.near,e=t*Math.tan(Ca*.5*this.fov)/this.zoom,i=2*e,s=this.aspect*i,r=-.5*s,o=this.view;if(this.view!==null&&this.view.enabled){let l=o.fullWidth,c=o.fullHeight;r+=o.offsetX*s/l,e-=o.offsetY*i/c,s*=o.width/l,i*=o.height/c}let a=this.filmOffset;a!==0&&(r+=t*a/this.getFilmWidth()),this.projectionMatrix.makePerspective(r,r+s,e,e-i,t,this.far,this.coordinateSystem),this.projectionMatrixInverse.copy(this.projectionMatrix).invert()}toJSON(t){let e=super.toJSON(t);return e.object.fov=this.fov,e.object.zoom=this.zoom,e.object.near=this.near,e.object.far=this.far,e.object.focus=this.focus,e.object.aspect=this.aspect,this.view!==null&&(e.object.view=Object.assign({},this.view)),e.object.filmGauge=this.filmGauge,e.object.filmOffset=this.filmOffset,e}},ji=-90,ts=1,Kl=class extends Oe{constructor(t,e,i){super(),this.type="CubeCamera",this.renderTarget=i,this.coordinateSystem=null,this.activeMipmapLevel=0;let s=new Me(ji,ts,t,e);s.layers=this.layers,this.add(s);let r=new Me(ji,ts,t,e);r.layers=this.layers,this.add(r);let o=new Me(ji,ts,t,e);o.layers=this.layers,this.add(o);let a=new Me(ji,ts,t,e);a.layers=this.layers,this.add(a);let l=new Me(ji,ts,t,e);l.layers=this.layers,this.add(l);let c=new Me(ji,ts,t,e);c.layers=this.layers,this.add(c)}updateCoordinateSystem(){let t=this.coordinateSystem,e=this.children.concat(),[i,s,r,o,a,l]=e;for(let c of e)this.remove(c);if(t===Sn)i.up.set(0,1,0),i.lookAt(1,0,0),s.up.set(0,1,0),s.lookAt(-1,0,0),r.up.set(0,0,-1),r.lookAt(0,1,0),o.up.set(0,0,1),o.lookAt(0,-1,0),a.up.set(0,1,0),a.lookAt(0,0,1),l.up.set(0,1,0),l.lookAt(0,0,-1);else if(t===To)i.up.set(0,-1,0),i.lookAt(-1,0,0),s.up.set(0,-1,0),s.lookAt(1,0,0),r.up.set(0,0,1),r.lookAt(0,1,0),o.up.set(0,0,-1),o.lookAt(0,-1,0),a.up.set(0,-1,0),a.lookAt(0,0,1),l.up.set(0,-1,0),l.lookAt(0,0,-1);else throw new Error("THREE.CubeCamera.updateCoordinateSystem(): Invalid coordinate system: "+t);for(let c of e)this.add(c),c.updateMatrixWorld()}update(t,e){this.parent===null&&this.updateMatrixWorld();let{renderTarget:i,activeMipmapLevel:s}=this;this.coordinateSystem!==t.coordinateSystem&&(this.coordinateSystem=t.coordinateSystem,this.updateCoordinateSystem());let[r,o,a,l,c,h]=this.children,d=t.getRenderTarget(),p=t.getActiveCubeFace(),m=t.getActiveMipmapLevel(),g=t.xr.enabled;t.xr.enabled=!1;let _=i.texture.generateMipmaps;i.texture.generateMipmaps=!1,t.setRenderTarget(i,0,s),t.render(e,r),t.setRenderTarget(i,1,s),t.render(e,o),t.setRenderTarget(i,2,s),t.render(e,a),t.setRenderTarget(i,3,s),t.render(e,l),t.setRenderTarget(i,4,s),t.render(e,c),i.texture.generateMipmaps=_,t.setRenderTarget(i,5,s),t.render(e,h),t.setRenderTarget(d,p,m),t.xr.enabled=g,i.texture.needsPMREMUpdate=!0}},Uo=class extends Xe{constructor(t,e,i,s,r,o,a,l,c,h){t=t!==void 0?t:[],e=e!==void 0?e:cs,super(t,e,i,s,r,o,a,l,c,h),this.isCubeTexture=!0,this.flipY=!1}get images(){return this.image}set images(t){this.image=t}},$l=class extends En{constructor(t=1,e={}){super(t,t,e),this.isWebGLCubeRenderTarget=!0;let i={width:t,height:t,depth:1},s=[i,i,i,i,i,i];this.texture=new Uo(s,e.mapping,e.wrapS,e.wrapT,e.magFilter,e.minFilter,e.format,e.type,e.anisotropy,e.colorSpace),this.texture.isRenderTargetTexture=!0,this.texture.generateMipmaps=e.generateMipmaps!==void 0?e.generateMipmaps:!1,this.texture.minFilter=e.minFilter!==void 0?e.minFilter:Qe}fromEquirectangularTexture(t,e){this.texture.type=e.type,this.texture.colorSpace=e.colorSpace,this.texture.generateMipmaps=e.generateMipmaps,this.texture.minFilter=e.minFilter,this.texture.magFilter=e.magFilter;let i={uniforms:{tEquirect:{value:null}},vertexShader:`

				varying vec3 vWorldDirection;

				vec3 transformDirection( in vec3 dir, in mat4 matrix ) {

					return normalize( ( matrix * vec4( dir, 0.0 ) ).xyz );

				}

				void main() {

					vWorldDirection = transformDirection( position, modelMatrix );

					#include <begin_vertex>
					#include <project_vertex>

				}
			`,fragmentShader:`

				uniform sampler2D tEquirect;

				varying vec3 vWorldDirection;

				#include <common>

				void main() {

					vec3 direction = normalize( vWorldDirection );

					vec2 sampleUV = equirectUv( direction );

					gl_FragColor = texture2D( tEquirect, sampleUV );

				}
			`},s=new Hs(5,5,5),r=new cn({name:"CubemapFromEquirect",uniforms:ps(i.uniforms),vertexShader:i.vertexShader,fragmentShader:i.fragmentShader,side:Re,blending:Gn});r.uniforms.tEquirect.value=e;let o=new Fe(s,r),a=e.minFilter;return e.minFilter===di&&(e.minFilter=Qe),new Kl(1,10,this).update(t,o),e.minFilter=a,o.geometry.dispose(),o.material.dispose(),this}clear(t,e,i,s){let r=t.getRenderTarget();for(let o=0;o<6;o++)t.setRenderTarget(this,o),t.clear(e,i,s);t.setRenderTarget(r)}},Za=new F,Xm=new F,qm=new Ft,yn=class{constructor(t=new F(1,0,0),e=0){this.isPlane=!0,this.normal=t,this.constant=e}set(t,e){return this.normal.copy(t),this.constant=e,this}setComponents(t,e,i,s){return this.normal.set(t,e,i),this.constant=s,this}setFromNormalAndCoplanarPoint(t,e){return this.normal.copy(t),this.constant=-e.dot(this.normal),this}setFromCoplanarPoints(t,e,i){let s=Za.subVectors(i,e).cross(Xm.subVectors(t,e)).normalize();return this.setFromNormalAndCoplanarPoint(s,t),this}copy(t){return this.normal.copy(t.normal),this.constant=t.constant,this}normalize(){let t=1/this.normal.length();return this.normal.multiplyScalar(t),this.constant*=t,this}negate(){return this.constant*=-1,this.normal.negate(),this}distanceToPoint(t){return this.normal.dot(t)+this.constant}distanceToSphere(t){return this.distanceToPoint(t.center)-t.radius}projectPoint(t,e){return e.copy(t).addScaledVector(this.normal,-this.distanceToPoint(t))}intersectLine(t,e){let i=t.delta(Za),s=this.normal.dot(i);if(s===0)return this.distanceToPoint(t.start)===0?e.copy(t.start):null;let r=-(t.start.dot(this.normal)+this.constant)/s;return r<0||r>1?null:e.copy(t.start).addScaledVector(i,r)}intersectsLine(t){let e=this.distanceToPoint(t.start),i=this.distanceToPoint(t.end);return e<0&&i>0||i<0&&e>0}intersectsBox(t){return t.intersectsPlane(this)}intersectsSphere(t){return t.intersectsPlane(this)}coplanarPoint(t){return t.copy(this.normal).multiplyScalar(-this.constant)}applyMatrix4(t,e){let i=e||qm.getNormalMatrix(t),s=this.coplanarPoint(Za).applyMatrix4(t),r=this.normal.applyMatrix3(i).normalize();return this.constant=-s.dot(r),this}translate(t){return this.constant-=t.dot(this.normal),this}equals(t){return t.normal.equals(this.normal)&&t.constant===this.constant}clone(){return new this.constructor().copy(this)}},ri=new gi,ro=new F,No=class{constructor(t=new yn,e=new yn,i=new yn,s=new yn,r=new yn,o=new yn){this.planes=[t,e,i,s,r,o]}set(t,e,i,s,r,o){let a=this.planes;return a[0].copy(t),a[1].copy(e),a[2].copy(i),a[3].copy(s),a[4].copy(r),a[5].copy(o),this}copy(t){let e=this.planes;for(let i=0;i<6;i++)e[i].copy(t.planes[i]);return this}setFromProjectionMatrix(t,e=Sn){let i=this.planes,s=t.elements,r=s[0],o=s[1],a=s[2],l=s[3],c=s[4],h=s[5],d=s[6],p=s[7],m=s[8],g=s[9],_=s[10],u=s[11],f=s[12],b=s[13],S=s[14],E=s[15];if(i[0].setComponents(l-r,p-c,u-m,E-f).normalize(),i[1].setComponents(l+r,p+c,u+m,E+f).normalize(),i[2].setComponents(l+o,p+h,u+g,E+b).normalize(),i[3].setComponents(l-o,p-h,u-g,E-b).normalize(),i[4].setComponents(l-a,p-d,u-_,E-S).normalize(),e===Sn)i[5].setComponents(l+a,p+d,u+_,E+S).normalize();else if(e===To)i[5].setComponents(a,d,_,S).normalize();else throw new Error("THREE.Frustum.setFromProjectionMatrix(): Invalid coordinate system: "+e);return this}intersectsObject(t){if(t.boundingSphere!==void 0)t.boundingSphere===null&&t.computeBoundingSphere(),ri.copy(t.boundingSphere).applyMatrix4(t.matrixWorld);else{let e=t.geometry;e.boundingSphere===null&&e.computeBoundingSphere(),ri.copy(e.boundingSphere).applyMatrix4(t.matrixWorld)}return this.intersectsSphere(ri)}intersectsSprite(t){return ri.center.set(0,0,0),ri.radius=.7071067811865476,ri.applyMatrix4(t.matrixWorld),this.intersectsSphere(ri)}intersectsSphere(t){let e=this.planes,i=t.center,s=-t.radius;for(let r=0;r<6;r++)if(e[r].distanceToPoint(i)<s)return!1;return!0}intersectsBox(t){let e=this.planes;for(let i=0;i<6;i++){let s=e[i];if(ro.x=s.normal.x>0?t.max.x:t.min.x,ro.y=s.normal.y>0?t.max.y:t.min.y,ro.z=s.normal.z>0?t.max.z:t.min.z,s.distanceToPoint(ro)<0)return!1}return!0}containsPoint(t){let e=this.planes;for(let i=0;i<6;i++)if(e[i].distanceToPoint(t)<0)return!1;return!0}clone(){return new this.constructor().copy(this)}};function Qf(){let n=null,t=!1,e=null,i=null;function s(r,o){e(r,o),i=n.requestAnimationFrame(s)}return{start:function(){t!==!0&&e!==null&&(i=n.requestAnimationFrame(s),t=!0)},stop:function(){n.cancelAnimationFrame(i),t=!1},setAnimationLoop:function(r){e=r},setContext:function(r){n=r}}}function Ym(n){let t=new WeakMap;function e(a,l){let c=a.array,h=a.usage,d=c.byteLength,p=n.createBuffer();n.bindBuffer(l,p),n.bufferData(l,c,h),a.onUploadCallback();let m;if(c instanceof Float32Array)m=n.FLOAT;else if(c instanceof Uint16Array)a.isFloat16BufferAttribute?m=n.HALF_FLOAT:m=n.UNSIGNED_SHORT;else if(c instanceof Int16Array)m=n.SHORT;else if(c instanceof Uint32Array)m=n.UNSIGNED_INT;else if(c instanceof Int32Array)m=n.INT;else if(c instanceof Int8Array)m=n.BYTE;else if(c instanceof Uint8Array)m=n.UNSIGNED_BYTE;else if(c instanceof Uint8ClampedArray)m=n.UNSIGNED_BYTE;else throw new Error("THREE.WebGLAttributes: Unsupported buffer data format: "+c);return{buffer:p,type:m,bytesPerElement:c.BYTES_PER_ELEMENT,version:a.version,size:d}}function i(a,l,c){let h=l.array,d=l.updateRanges;if(n.bindBuffer(c,a),d.length===0)n.bufferSubData(c,0,h);else{d.sort((m,g)=>m.start-g.start);let p=0;for(let m=1;m<d.length;m++){let g=d[p],_=d[m];_.start<=g.start+g.count+1?g.count=Math.max(g.count,_.start+_.count-g.start):(++p,d[p]=_)}d.length=p+1;for(let m=0,g=d.length;m<g;m++){let _=d[m];n.bufferSubData(c,_.start*h.BYTES_PER_ELEMENT,h,_.start,_.count)}l.clearUpdateRanges()}l.onUploadCallback()}function s(a){return a.isInterleavedBufferAttribute&&(a=a.data),t.get(a)}function r(a){a.isInterleavedBufferAttribute&&(a=a.data);let l=t.get(a);l&&(n.deleteBuffer(l.buffer),t.delete(a))}function o(a,l){if(a.isInterleavedBufferAttribute&&(a=a.data),a.isGLBufferAttribute){let h=t.get(a);(!h||h.version<a.version)&&t.set(a,{buffer:a.buffer,type:a.type,bytesPerElement:a.elementSize,version:a.version});return}let c=t.get(a);if(c===void 0)t.set(a,e(a,l));else if(c.version<a.version){if(c.size!==a.array.byteLength)throw new Error("THREE.WebGLAttributes: The size of the buffer attribute's array buffer does not match the original size. Resizing buffer attributes is not supported.");i(c.buffer,a,l),c.version=a.version}}return{get:s,remove:r,update:o}}var Fo=class n extends Be{constructor(t=1,e=1,i=1,s=1){super(),this.type="PlaneGeometry",this.parameters={width:t,height:e,widthSegments:i,heightSegments:s};let r=t/2,o=e/2,a=Math.floor(i),l=Math.floor(s),c=a+1,h=l+1,d=t/a,p=e/l,m=[],g=[],_=[],u=[];for(let f=0;f<h;f++){let b=f*p-o;for(let S=0;S<c;S++){let E=S*d-r;g.push(E,-b,0),_.push(0,0,1),u.push(S/a),u.push(1-f/l)}}for(let f=0;f<l;f++)for(let b=0;b<a;b++){let S=b+c*f,E=b+c*(f+1),C=b+1+c*(f+1),A=b+1+c*f;m.push(S,E,A),m.push(E,C,A)}this.setIndex(m),this.setAttribute("position",new Pe(g,3)),this.setAttribute("normal",new Pe(_,3)),this.setAttribute("uv",new Pe(u,2))}copy(t){return super.copy(t),this.parameters=Object.assign({},t.parameters),this}static fromJSON(t){return new n(t.width,t.height,t.widthSegments,t.heightSegments)}},Km=`#ifdef USE_ALPHAHASH
	if ( diffuseColor.a < getAlphaHashThreshold( vPosition ) ) discard;
#endif`,$m=`#ifdef USE_ALPHAHASH
	const float ALPHA_HASH_SCALE = 0.05;
	float hash2D( vec2 value ) {
		return fract( 1.0e4 * sin( 17.0 * value.x + 0.1 * value.y ) * ( 0.1 + abs( sin( 13.0 * value.y + value.x ) ) ) );
	}
	float hash3D( vec3 value ) {
		return hash2D( vec2( hash2D( value.xy ), value.z ) );
	}
	float getAlphaHashThreshold( vec3 position ) {
		float maxDeriv = max(
			length( dFdx( position.xyz ) ),
			length( dFdy( position.xyz ) )
		);
		float pixScale = 1.0 / ( ALPHA_HASH_SCALE * maxDeriv );
		vec2 pixScales = vec2(
			exp2( floor( log2( pixScale ) ) ),
			exp2( ceil( log2( pixScale ) ) )
		);
		vec2 alpha = vec2(
			hash3D( floor( pixScales.x * position.xyz ) ),
			hash3D( floor( pixScales.y * position.xyz ) )
		);
		float lerpFactor = fract( log2( pixScale ) );
		float x = ( 1.0 - lerpFactor ) * alpha.x + lerpFactor * alpha.y;
		float a = min( lerpFactor, 1.0 - lerpFactor );
		vec3 cases = vec3(
			x * x / ( 2.0 * a * ( 1.0 - a ) ),
			( x - 0.5 * a ) / ( 1.0 - a ),
			1.0 - ( ( 1.0 - x ) * ( 1.0 - x ) / ( 2.0 * a * ( 1.0 - a ) ) )
		);
		float threshold = ( x < ( 1.0 - a ) )
			? ( ( x < a ) ? cases.x : cases.y )
			: cases.z;
		return clamp( threshold , 1.0e-6, 1.0 );
	}
#endif`,Zm=`#ifdef USE_ALPHAMAP
	diffuseColor.a *= texture2D( alphaMap, vAlphaMapUv ).g;
#endif`,Jm=`#ifdef USE_ALPHAMAP
	uniform sampler2D alphaMap;
#endif`,Qm=`#ifdef USE_ALPHATEST
	#ifdef ALPHA_TO_COVERAGE
	diffuseColor.a = smoothstep( alphaTest, alphaTest + fwidth( diffuseColor.a ), diffuseColor.a );
	if ( diffuseColor.a == 0.0 ) discard;
	#else
	if ( diffuseColor.a < alphaTest ) discard;
	#endif
#endif`,jm=`#ifdef USE_ALPHATEST
	uniform float alphaTest;
#endif`,tg=`#ifdef USE_AOMAP
	float ambientOcclusion = ( texture2D( aoMap, vAoMapUv ).r - 1.0 ) * aoMapIntensity + 1.0;
	reflectedLight.indirectDiffuse *= ambientOcclusion;
	#if defined( USE_CLEARCOAT ) 
		clearcoatSpecularIndirect *= ambientOcclusion;
	#endif
	#if defined( USE_SHEEN ) 
		sheenSpecularIndirect *= ambientOcclusion;
	#endif
	#if defined( USE_ENVMAP ) && defined( STANDARD )
		float dotNV = saturate( dot( geometryNormal, geometryViewDir ) );
		reflectedLight.indirectSpecular *= computeSpecularOcclusion( dotNV, ambientOcclusion, material.roughness );
	#endif
#endif`,eg=`#ifdef USE_AOMAP
	uniform sampler2D aoMap;
	uniform float aoMapIntensity;
#endif`,ng=`#ifdef USE_BATCHING
	#if ! defined( GL_ANGLE_multi_draw )
	#define gl_DrawID _gl_DrawID
	uniform int _gl_DrawID;
	#endif
	uniform highp sampler2D batchingTexture;
	uniform highp usampler2D batchingIdTexture;
	mat4 getBatchingMatrix( const in float i ) {
		int size = textureSize( batchingTexture, 0 ).x;
		int j = int( i ) * 4;
		int x = j % size;
		int y = j / size;
		vec4 v1 = texelFetch( batchingTexture, ivec2( x, y ), 0 );
		vec4 v2 = texelFetch( batchingTexture, ivec2( x + 1, y ), 0 );
		vec4 v3 = texelFetch( batchingTexture, ivec2( x + 2, y ), 0 );
		vec4 v4 = texelFetch( batchingTexture, ivec2( x + 3, y ), 0 );
		return mat4( v1, v2, v3, v4 );
	}
	float getIndirectIndex( const in int i ) {
		int size = textureSize( batchingIdTexture, 0 ).x;
		int x = i % size;
		int y = i / size;
		return float( texelFetch( batchingIdTexture, ivec2( x, y ), 0 ).r );
	}
#endif
#ifdef USE_BATCHING_COLOR
	uniform sampler2D batchingColorTexture;
	vec3 getBatchingColor( const in float i ) {
		int size = textureSize( batchingColorTexture, 0 ).x;
		int j = int( i );
		int x = j % size;
		int y = j / size;
		return texelFetch( batchingColorTexture, ivec2( x, y ), 0 ).rgb;
	}
#endif`,ig=`#ifdef USE_BATCHING
	mat4 batchingMatrix = getBatchingMatrix( getIndirectIndex( gl_DrawID ) );
#endif`,sg=`vec3 transformed = vec3( position );
#ifdef USE_ALPHAHASH
	vPosition = vec3( position );
#endif`,rg=`vec3 objectNormal = vec3( normal );
#ifdef USE_TANGENT
	vec3 objectTangent = vec3( tangent.xyz );
#endif`,og=`float G_BlinnPhong_Implicit( ) {
	return 0.25;
}
float D_BlinnPhong( const in float shininess, const in float dotNH ) {
	return RECIPROCAL_PI * ( shininess * 0.5 + 1.0 ) * pow( dotNH, shininess );
}
vec3 BRDF_BlinnPhong( const in vec3 lightDir, const in vec3 viewDir, const in vec3 normal, const in vec3 specularColor, const in float shininess ) {
	vec3 halfDir = normalize( lightDir + viewDir );
	float dotNH = saturate( dot( normal, halfDir ) );
	float dotVH = saturate( dot( viewDir, halfDir ) );
	vec3 F = F_Schlick( specularColor, 1.0, dotVH );
	float G = G_BlinnPhong_Implicit( );
	float D = D_BlinnPhong( shininess, dotNH );
	return F * ( G * D );
} // validated`,ag=`#ifdef USE_IRIDESCENCE
	const mat3 XYZ_TO_REC709 = mat3(
		 3.2404542, -0.9692660,  0.0556434,
		-1.5371385,  1.8760108, -0.2040259,
		-0.4985314,  0.0415560,  1.0572252
	);
	vec3 Fresnel0ToIor( vec3 fresnel0 ) {
		vec3 sqrtF0 = sqrt( fresnel0 );
		return ( vec3( 1.0 ) + sqrtF0 ) / ( vec3( 1.0 ) - sqrtF0 );
	}
	vec3 IorToFresnel0( vec3 transmittedIor, float incidentIor ) {
		return pow2( ( transmittedIor - vec3( incidentIor ) ) / ( transmittedIor + vec3( incidentIor ) ) );
	}
	float IorToFresnel0( float transmittedIor, float incidentIor ) {
		return pow2( ( transmittedIor - incidentIor ) / ( transmittedIor + incidentIor ));
	}
	vec3 evalSensitivity( float OPD, vec3 shift ) {
		float phase = 2.0 * PI * OPD * 1.0e-9;
		vec3 val = vec3( 5.4856e-13, 4.4201e-13, 5.2481e-13 );
		vec3 pos = vec3( 1.6810e+06, 1.7953e+06, 2.2084e+06 );
		vec3 var = vec3( 4.3278e+09, 9.3046e+09, 6.6121e+09 );
		vec3 xyz = val * sqrt( 2.0 * PI * var ) * cos( pos * phase + shift ) * exp( - pow2( phase ) * var );
		xyz.x += 9.7470e-14 * sqrt( 2.0 * PI * 4.5282e+09 ) * cos( 2.2399e+06 * phase + shift[ 0 ] ) * exp( - 4.5282e+09 * pow2( phase ) );
		xyz /= 1.0685e-7;
		vec3 rgb = XYZ_TO_REC709 * xyz;
		return rgb;
	}
	vec3 evalIridescence( float outsideIOR, float eta2, float cosTheta1, float thinFilmThickness, vec3 baseF0 ) {
		vec3 I;
		float iridescenceIOR = mix( outsideIOR, eta2, smoothstep( 0.0, 0.03, thinFilmThickness ) );
		float sinTheta2Sq = pow2( outsideIOR / iridescenceIOR ) * ( 1.0 - pow2( cosTheta1 ) );
		float cosTheta2Sq = 1.0 - sinTheta2Sq;
		if ( cosTheta2Sq < 0.0 ) {
			return vec3( 1.0 );
		}
		float cosTheta2 = sqrt( cosTheta2Sq );
		float R0 = IorToFresnel0( iridescenceIOR, outsideIOR );
		float R12 = F_Schlick( R0, 1.0, cosTheta1 );
		float T121 = 1.0 - R12;
		float phi12 = 0.0;
		if ( iridescenceIOR < outsideIOR ) phi12 = PI;
		float phi21 = PI - phi12;
		vec3 baseIOR = Fresnel0ToIor( clamp( baseF0, 0.0, 0.9999 ) );		vec3 R1 = IorToFresnel0( baseIOR, iridescenceIOR );
		vec3 R23 = F_Schlick( R1, 1.0, cosTheta2 );
		vec3 phi23 = vec3( 0.0 );
		if ( baseIOR[ 0 ] < iridescenceIOR ) phi23[ 0 ] = PI;
		if ( baseIOR[ 1 ] < iridescenceIOR ) phi23[ 1 ] = PI;
		if ( baseIOR[ 2 ] < iridescenceIOR ) phi23[ 2 ] = PI;
		float OPD = 2.0 * iridescenceIOR * thinFilmThickness * cosTheta2;
		vec3 phi = vec3( phi21 ) + phi23;
		vec3 R123 = clamp( R12 * R23, 1e-5, 0.9999 );
		vec3 r123 = sqrt( R123 );
		vec3 Rs = pow2( T121 ) * R23 / ( vec3( 1.0 ) - R123 );
		vec3 C0 = R12 + Rs;
		I = C0;
		vec3 Cm = Rs - T121;
		for ( int m = 1; m <= 2; ++ m ) {
			Cm *= r123;
			vec3 Sm = 2.0 * evalSensitivity( float( m ) * OPD, float( m ) * phi );
			I += Cm * Sm;
		}
		return max( I, vec3( 0.0 ) );
	}
#endif`,lg=`#ifdef USE_BUMPMAP
	uniform sampler2D bumpMap;
	uniform float bumpScale;
	vec2 dHdxy_fwd() {
		vec2 dSTdx = dFdx( vBumpMapUv );
		vec2 dSTdy = dFdy( vBumpMapUv );
		float Hll = bumpScale * texture2D( bumpMap, vBumpMapUv ).x;
		float dBx = bumpScale * texture2D( bumpMap, vBumpMapUv + dSTdx ).x - Hll;
		float dBy = bumpScale * texture2D( bumpMap, vBumpMapUv + dSTdy ).x - Hll;
		return vec2( dBx, dBy );
	}
	vec3 perturbNormalArb( vec3 surf_pos, vec3 surf_norm, vec2 dHdxy, float faceDirection ) {
		vec3 vSigmaX = normalize( dFdx( surf_pos.xyz ) );
		vec3 vSigmaY = normalize( dFdy( surf_pos.xyz ) );
		vec3 vN = surf_norm;
		vec3 R1 = cross( vSigmaY, vN );
		vec3 R2 = cross( vN, vSigmaX );
		float fDet = dot( vSigmaX, R1 ) * faceDirection;
		vec3 vGrad = sign( fDet ) * ( dHdxy.x * R1 + dHdxy.y * R2 );
		return normalize( abs( fDet ) * surf_norm - vGrad );
	}
#endif`,cg=`#if NUM_CLIPPING_PLANES > 0
	vec4 plane;
	#ifdef ALPHA_TO_COVERAGE
		float distanceToPlane, distanceGradient;
		float clipOpacity = 1.0;
		#pragma unroll_loop_start
		for ( int i = 0; i < UNION_CLIPPING_PLANES; i ++ ) {
			plane = clippingPlanes[ i ];
			distanceToPlane = - dot( vClipPosition, plane.xyz ) + plane.w;
			distanceGradient = fwidth( distanceToPlane ) / 2.0;
			clipOpacity *= smoothstep( - distanceGradient, distanceGradient, distanceToPlane );
			if ( clipOpacity == 0.0 ) discard;
		}
		#pragma unroll_loop_end
		#if UNION_CLIPPING_PLANES < NUM_CLIPPING_PLANES
			float unionClipOpacity = 1.0;
			#pragma unroll_loop_start
			for ( int i = UNION_CLIPPING_PLANES; i < NUM_CLIPPING_PLANES; i ++ ) {
				plane = clippingPlanes[ i ];
				distanceToPlane = - dot( vClipPosition, plane.xyz ) + plane.w;
				distanceGradient = fwidth( distanceToPlane ) / 2.0;
				unionClipOpacity *= 1.0 - smoothstep( - distanceGradient, distanceGradient, distanceToPlane );
			}
			#pragma unroll_loop_end
			clipOpacity *= 1.0 - unionClipOpacity;
		#endif
		diffuseColor.a *= clipOpacity;
		if ( diffuseColor.a == 0.0 ) discard;
	#else
		#pragma unroll_loop_start
		for ( int i = 0; i < UNION_CLIPPING_PLANES; i ++ ) {
			plane = clippingPlanes[ i ];
			if ( dot( vClipPosition, plane.xyz ) > plane.w ) discard;
		}
		#pragma unroll_loop_end
		#if UNION_CLIPPING_PLANES < NUM_CLIPPING_PLANES
			bool clipped = true;
			#pragma unroll_loop_start
			for ( int i = UNION_CLIPPING_PLANES; i < NUM_CLIPPING_PLANES; i ++ ) {
				plane = clippingPlanes[ i ];
				clipped = ( dot( vClipPosition, plane.xyz ) > plane.w ) && clipped;
			}
			#pragma unroll_loop_end
			if ( clipped ) discard;
		#endif
	#endif
#endif`,hg=`#if NUM_CLIPPING_PLANES > 0
	varying vec3 vClipPosition;
	uniform vec4 clippingPlanes[ NUM_CLIPPING_PLANES ];
#endif`,ug=`#if NUM_CLIPPING_PLANES > 0
	varying vec3 vClipPosition;
#endif`,fg=`#if NUM_CLIPPING_PLANES > 0
	vClipPosition = - mvPosition.xyz;
#endif`,dg=`#if defined( USE_COLOR_ALPHA )
	diffuseColor *= vColor;
#elif defined( USE_COLOR )
	diffuseColor.rgb *= vColor;
#endif`,pg=`#if defined( USE_COLOR_ALPHA )
	varying vec4 vColor;
#elif defined( USE_COLOR )
	varying vec3 vColor;
#endif`,mg=`#if defined( USE_COLOR_ALPHA )
	varying vec4 vColor;
#elif defined( USE_COLOR ) || defined( USE_INSTANCING_COLOR ) || defined( USE_BATCHING_COLOR )
	varying vec3 vColor;
#endif`,gg=`#if defined( USE_COLOR_ALPHA )
	vColor = vec4( 1.0 );
#elif defined( USE_COLOR ) || defined( USE_INSTANCING_COLOR ) || defined( USE_BATCHING_COLOR )
	vColor = vec3( 1.0 );
#endif
#ifdef USE_COLOR
	vColor *= color;
#endif
#ifdef USE_INSTANCING_COLOR
	vColor.xyz *= instanceColor.xyz;
#endif
#ifdef USE_BATCHING_COLOR
	vec3 batchingColor = getBatchingColor( getIndirectIndex( gl_DrawID ) );
	vColor.xyz *= batchingColor.xyz;
#endif`,xg=`#define PI 3.141592653589793
#define PI2 6.283185307179586
#define PI_HALF 1.5707963267948966
#define RECIPROCAL_PI 0.3183098861837907
#define RECIPROCAL_PI2 0.15915494309189535
#define EPSILON 1e-6
#ifndef saturate
#define saturate( a ) clamp( a, 0.0, 1.0 )
#endif
#define whiteComplement( a ) ( 1.0 - saturate( a ) )
float pow2( const in float x ) { return x*x; }
vec3 pow2( const in vec3 x ) { return x*x; }
float pow3( const in float x ) { return x*x*x; }
float pow4( const in float x ) { float x2 = x*x; return x2*x2; }
float max3( const in vec3 v ) { return max( max( v.x, v.y ), v.z ); }
float average( const in vec3 v ) { return dot( v, vec3( 0.3333333 ) ); }
highp float rand( const in vec2 uv ) {
	const highp float a = 12.9898, b = 78.233, c = 43758.5453;
	highp float dt = dot( uv.xy, vec2( a,b ) ), sn = mod( dt, PI );
	return fract( sin( sn ) * c );
}
#ifdef HIGH_PRECISION
	float precisionSafeLength( vec3 v ) { return length( v ); }
#else
	float precisionSafeLength( vec3 v ) {
		float maxComponent = max3( abs( v ) );
		return length( v / maxComponent ) * maxComponent;
	}
#endif
struct IncidentLight {
	vec3 color;
	vec3 direction;
	bool visible;
};
struct ReflectedLight {
	vec3 directDiffuse;
	vec3 directSpecular;
	vec3 indirectDiffuse;
	vec3 indirectSpecular;
};
#ifdef USE_ALPHAHASH
	varying vec3 vPosition;
#endif
vec3 transformDirection( in vec3 dir, in mat4 matrix ) {
	return normalize( ( matrix * vec4( dir, 0.0 ) ).xyz );
}
vec3 inverseTransformDirection( in vec3 dir, in mat4 matrix ) {
	return normalize( ( vec4( dir, 0.0 ) * matrix ).xyz );
}
mat3 transposeMat3( const in mat3 m ) {
	mat3 tmp;
	tmp[ 0 ] = vec3( m[ 0 ].x, m[ 1 ].x, m[ 2 ].x );
	tmp[ 1 ] = vec3( m[ 0 ].y, m[ 1 ].y, m[ 2 ].y );
	tmp[ 2 ] = vec3( m[ 0 ].z, m[ 1 ].z, m[ 2 ].z );
	return tmp;
}
bool isPerspectiveMatrix( mat4 m ) {
	return m[ 2 ][ 3 ] == - 1.0;
}
vec2 equirectUv( in vec3 dir ) {
	float u = atan( dir.z, dir.x ) * RECIPROCAL_PI2 + 0.5;
	float v = asin( clamp( dir.y, - 1.0, 1.0 ) ) * RECIPROCAL_PI + 0.5;
	return vec2( u, v );
}
vec3 BRDF_Lambert( const in vec3 diffuseColor ) {
	return RECIPROCAL_PI * diffuseColor;
}
vec3 F_Schlick( const in vec3 f0, const in float f90, const in float dotVH ) {
	float fresnel = exp2( ( - 5.55473 * dotVH - 6.98316 ) * dotVH );
	return f0 * ( 1.0 - fresnel ) + ( f90 * fresnel );
}
float F_Schlick( const in float f0, const in float f90, const in float dotVH ) {
	float fresnel = exp2( ( - 5.55473 * dotVH - 6.98316 ) * dotVH );
	return f0 * ( 1.0 - fresnel ) + ( f90 * fresnel );
} // validated`,_g=`#ifdef ENVMAP_TYPE_CUBE_UV
	#define cubeUV_minMipLevel 4.0
	#define cubeUV_minTileSize 16.0
	float getFace( vec3 direction ) {
		vec3 absDirection = abs( direction );
		float face = - 1.0;
		if ( absDirection.x > absDirection.z ) {
			if ( absDirection.x > absDirection.y )
				face = direction.x > 0.0 ? 0.0 : 3.0;
			else
				face = direction.y > 0.0 ? 1.0 : 4.0;
		} else {
			if ( absDirection.z > absDirection.y )
				face = direction.z > 0.0 ? 2.0 : 5.0;
			else
				face = direction.y > 0.0 ? 1.0 : 4.0;
		}
		return face;
	}
	vec2 getUV( vec3 direction, float face ) {
		vec2 uv;
		if ( face == 0.0 ) {
			uv = vec2( direction.z, direction.y ) / abs( direction.x );
		} else if ( face == 1.0 ) {
			uv = vec2( - direction.x, - direction.z ) / abs( direction.y );
		} else if ( face == 2.0 ) {
			uv = vec2( - direction.x, direction.y ) / abs( direction.z );
		} else if ( face == 3.0 ) {
			uv = vec2( - direction.z, direction.y ) / abs( direction.x );
		} else if ( face == 4.0 ) {
			uv = vec2( - direction.x, direction.z ) / abs( direction.y );
		} else {
			uv = vec2( direction.x, direction.y ) / abs( direction.z );
		}
		return 0.5 * ( uv + 1.0 );
	}
	vec3 bilinearCubeUV( sampler2D envMap, vec3 direction, float mipInt ) {
		float face = getFace( direction );
		float filterInt = max( cubeUV_minMipLevel - mipInt, 0.0 );
		mipInt = max( mipInt, cubeUV_minMipLevel );
		float faceSize = exp2( mipInt );
		highp vec2 uv = getUV( direction, face ) * ( faceSize - 2.0 ) + 1.0;
		if ( face > 2.0 ) {
			uv.y += faceSize;
			face -= 3.0;
		}
		uv.x += face * faceSize;
		uv.x += filterInt * 3.0 * cubeUV_minTileSize;
		uv.y += 4.0 * ( exp2( CUBEUV_MAX_MIP ) - faceSize );
		uv.x *= CUBEUV_TEXEL_WIDTH;
		uv.y *= CUBEUV_TEXEL_HEIGHT;
		#ifdef texture2DGradEXT
			return texture2DGradEXT( envMap, uv, vec2( 0.0 ), vec2( 0.0 ) ).rgb;
		#else
			return texture2D( envMap, uv ).rgb;
		#endif
	}
	#define cubeUV_r0 1.0
	#define cubeUV_m0 - 2.0
	#define cubeUV_r1 0.8
	#define cubeUV_m1 - 1.0
	#define cubeUV_r4 0.4
	#define cubeUV_m4 2.0
	#define cubeUV_r5 0.305
	#define cubeUV_m5 3.0
	#define cubeUV_r6 0.21
	#define cubeUV_m6 4.0
	float roughnessToMip( float roughness ) {
		float mip = 0.0;
		if ( roughness >= cubeUV_r1 ) {
			mip = ( cubeUV_r0 - roughness ) * ( cubeUV_m1 - cubeUV_m0 ) / ( cubeUV_r0 - cubeUV_r1 ) + cubeUV_m0;
		} else if ( roughness >= cubeUV_r4 ) {
			mip = ( cubeUV_r1 - roughness ) * ( cubeUV_m4 - cubeUV_m1 ) / ( cubeUV_r1 - cubeUV_r4 ) + cubeUV_m1;
		} else if ( roughness >= cubeUV_r5 ) {
			mip = ( cubeUV_r4 - roughness ) * ( cubeUV_m5 - cubeUV_m4 ) / ( cubeUV_r4 - cubeUV_r5 ) + cubeUV_m4;
		} else if ( roughness >= cubeUV_r6 ) {
			mip = ( cubeUV_r5 - roughness ) * ( cubeUV_m6 - cubeUV_m5 ) / ( cubeUV_r5 - cubeUV_r6 ) + cubeUV_m5;
		} else {
			mip = - 2.0 * log2( 1.16 * roughness );		}
		return mip;
	}
	vec4 textureCubeUV( sampler2D envMap, vec3 sampleDir, float roughness ) {
		float mip = clamp( roughnessToMip( roughness ), cubeUV_m0, CUBEUV_MAX_MIP );
		float mipF = fract( mip );
		float mipInt = floor( mip );
		vec3 color0 = bilinearCubeUV( envMap, sampleDir, mipInt );
		if ( mipF == 0.0 ) {
			return vec4( color0, 1.0 );
		} else {
			vec3 color1 = bilinearCubeUV( envMap, sampleDir, mipInt + 1.0 );
			return vec4( mix( color0, color1, mipF ), 1.0 );
		}
	}
#endif`,vg=`vec3 transformedNormal = objectNormal;
#ifdef USE_TANGENT
	vec3 transformedTangent = objectTangent;
#endif
#ifdef USE_BATCHING
	mat3 bm = mat3( batchingMatrix );
	transformedNormal /= vec3( dot( bm[ 0 ], bm[ 0 ] ), dot( bm[ 1 ], bm[ 1 ] ), dot( bm[ 2 ], bm[ 2 ] ) );
	transformedNormal = bm * transformedNormal;
	#ifdef USE_TANGENT
		transformedTangent = bm * transformedTangent;
	#endif
#endif
#ifdef USE_INSTANCING
	mat3 im = mat3( instanceMatrix );
	transformedNormal /= vec3( dot( im[ 0 ], im[ 0 ] ), dot( im[ 1 ], im[ 1 ] ), dot( im[ 2 ], im[ 2 ] ) );
	transformedNormal = im * transformedNormal;
	#ifdef USE_TANGENT
		transformedTangent = im * transformedTangent;
	#endif
#endif
transformedNormal = normalMatrix * transformedNormal;
#ifdef FLIP_SIDED
	transformedNormal = - transformedNormal;
#endif
#ifdef USE_TANGENT
	transformedTangent = ( modelViewMatrix * vec4( transformedTangent, 0.0 ) ).xyz;
	#ifdef FLIP_SIDED
		transformedTangent = - transformedTangent;
	#endif
#endif`,yg=`#ifdef USE_DISPLACEMENTMAP
	uniform sampler2D displacementMap;
	uniform float displacementScale;
	uniform float displacementBias;
#endif`,Mg=`#ifdef USE_DISPLACEMENTMAP
	transformed += normalize( objectNormal ) * ( texture2D( displacementMap, vDisplacementMapUv ).x * displacementScale + displacementBias );
#endif`,Sg=`#ifdef USE_EMISSIVEMAP
	vec4 emissiveColor = texture2D( emissiveMap, vEmissiveMapUv );
	totalEmissiveRadiance *= emissiveColor.rgb;
#endif`,bg=`#ifdef USE_EMISSIVEMAP
	uniform sampler2D emissiveMap;
#endif`,Eg="gl_FragColor = linearToOutputTexel( gl_FragColor );",wg=`
const mat3 LINEAR_SRGB_TO_LINEAR_DISPLAY_P3 = mat3(
	vec3( 0.8224621, 0.177538, 0.0 ),
	vec3( 0.0331941, 0.9668058, 0.0 ),
	vec3( 0.0170827, 0.0723974, 0.9105199 )
);
const mat3 LINEAR_DISPLAY_P3_TO_LINEAR_SRGB = mat3(
	vec3( 1.2249401, - 0.2249404, 0.0 ),
	vec3( - 0.0420569, 1.0420571, 0.0 ),
	vec3( - 0.0196376, - 0.0786361, 1.0982735 )
);
vec4 LinearSRGBToLinearDisplayP3( in vec4 value ) {
	return vec4( value.rgb * LINEAR_SRGB_TO_LINEAR_DISPLAY_P3, value.a );
}
vec4 LinearDisplayP3ToLinearSRGB( in vec4 value ) {
	return vec4( value.rgb * LINEAR_DISPLAY_P3_TO_LINEAR_SRGB, value.a );
}
vec4 LinearTransferOETF( in vec4 value ) {
	return value;
}
vec4 sRGBTransferOETF( in vec4 value ) {
	return vec4( mix( pow( value.rgb, vec3( 0.41666 ) ) * 1.055 - vec3( 0.055 ), value.rgb * 12.92, vec3( lessThanEqual( value.rgb, vec3( 0.0031308 ) ) ) ), value.a );
}`,Tg=`#ifdef USE_ENVMAP
	#ifdef ENV_WORLDPOS
		vec3 cameraToFrag;
		if ( isOrthographic ) {
			cameraToFrag = normalize( vec3( - viewMatrix[ 0 ][ 2 ], - viewMatrix[ 1 ][ 2 ], - viewMatrix[ 2 ][ 2 ] ) );
		} else {
			cameraToFrag = normalize( vWorldPosition - cameraPosition );
		}
		vec3 worldNormal = inverseTransformDirection( normal, viewMatrix );
		#ifdef ENVMAP_MODE_REFLECTION
			vec3 reflectVec = reflect( cameraToFrag, worldNormal );
		#else
			vec3 reflectVec = refract( cameraToFrag, worldNormal, refractionRatio );
		#endif
	#else
		vec3 reflectVec = vReflect;
	#endif
	#ifdef ENVMAP_TYPE_CUBE
		vec4 envColor = textureCube( envMap, envMapRotation * vec3( flipEnvMap * reflectVec.x, reflectVec.yz ) );
	#else
		vec4 envColor = vec4( 0.0 );
	#endif
	#ifdef ENVMAP_BLENDING_MULTIPLY
		outgoingLight = mix( outgoingLight, outgoingLight * envColor.xyz, specularStrength * reflectivity );
	#elif defined( ENVMAP_BLENDING_MIX )
		outgoingLight = mix( outgoingLight, envColor.xyz, specularStrength * reflectivity );
	#elif defined( ENVMAP_BLENDING_ADD )
		outgoingLight += envColor.xyz * specularStrength * reflectivity;
	#endif
#endif`,Ag=`#ifdef USE_ENVMAP
	uniform float envMapIntensity;
	uniform float flipEnvMap;
	uniform mat3 envMapRotation;
	#ifdef ENVMAP_TYPE_CUBE
		uniform samplerCube envMap;
	#else
		uniform sampler2D envMap;
	#endif
	
#endif`,Cg=`#ifdef USE_ENVMAP
	uniform float reflectivity;
	#if defined( USE_BUMPMAP ) || defined( USE_NORMALMAP ) || defined( PHONG ) || defined( LAMBERT )
		#define ENV_WORLDPOS
	#endif
	#ifdef ENV_WORLDPOS
		varying vec3 vWorldPosition;
		uniform float refractionRatio;
	#else
		varying vec3 vReflect;
	#endif
#endif`,Rg=`#ifdef USE_ENVMAP
	#if defined( USE_BUMPMAP ) || defined( USE_NORMALMAP ) || defined( PHONG ) || defined( LAMBERT )
		#define ENV_WORLDPOS
	#endif
	#ifdef ENV_WORLDPOS
		
		varying vec3 vWorldPosition;
	#else
		varying vec3 vReflect;
		uniform float refractionRatio;
	#endif
#endif`,Pg=`#ifdef USE_ENVMAP
	#ifdef ENV_WORLDPOS
		vWorldPosition = worldPosition.xyz;
	#else
		vec3 cameraToVertex;
		if ( isOrthographic ) {
			cameraToVertex = normalize( vec3( - viewMatrix[ 0 ][ 2 ], - viewMatrix[ 1 ][ 2 ], - viewMatrix[ 2 ][ 2 ] ) );
		} else {
			cameraToVertex = normalize( worldPosition.xyz - cameraPosition );
		}
		vec3 worldNormal = inverseTransformDirection( transformedNormal, viewMatrix );
		#ifdef ENVMAP_MODE_REFLECTION
			vReflect = reflect( cameraToVertex, worldNormal );
		#else
			vReflect = refract( cameraToVertex, worldNormal, refractionRatio );
		#endif
	#endif
#endif`,Ig=`#ifdef USE_FOG
	vFogDepth = - mvPosition.z;
#endif`,Lg=`#ifdef USE_FOG
	varying float vFogDepth;
#endif`,Dg=`#ifdef USE_FOG
	#ifdef FOG_EXP2
		float fogFactor = 1.0 - exp( - fogDensity * fogDensity * vFogDepth * vFogDepth );
	#else
		float fogFactor = smoothstep( fogNear, fogFar, vFogDepth );
	#endif
	gl_FragColor.rgb = mix( gl_FragColor.rgb, fogColor, fogFactor );
#endif`,Ug=`#ifdef USE_FOG
	uniform vec3 fogColor;
	varying float vFogDepth;
	#ifdef FOG_EXP2
		uniform float fogDensity;
	#else
		uniform float fogNear;
		uniform float fogFar;
	#endif
#endif`,Ng=`#ifdef USE_GRADIENTMAP
	uniform sampler2D gradientMap;
#endif
vec3 getGradientIrradiance( vec3 normal, vec3 lightDirection ) {
	float dotNL = dot( normal, lightDirection );
	vec2 coord = vec2( dotNL * 0.5 + 0.5, 0.0 );
	#ifdef USE_GRADIENTMAP
		return vec3( texture2D( gradientMap, coord ).r );
	#else
		vec2 fw = fwidth( coord ) * 0.5;
		return mix( vec3( 0.7 ), vec3( 1.0 ), smoothstep( 0.7 - fw.x, 0.7 + fw.x, coord.x ) );
	#endif
}`,Fg=`#ifdef USE_LIGHTMAP
	uniform sampler2D lightMap;
	uniform float lightMapIntensity;
#endif`,Og=`LambertMaterial material;
material.diffuseColor = diffuseColor.rgb;
material.specularStrength = specularStrength;`,Bg=`varying vec3 vViewPosition;
struct LambertMaterial {
	vec3 diffuseColor;
	float specularStrength;
};
void RE_Direct_Lambert( const in IncidentLight directLight, const in vec3 geometryPosition, const in vec3 geometryNormal, const in vec3 geometryViewDir, const in vec3 geometryClearcoatNormal, const in LambertMaterial material, inout ReflectedLight reflectedLight ) {
	float dotNL = saturate( dot( geometryNormal, directLight.direction ) );
	vec3 irradiance = dotNL * directLight.color;
	reflectedLight.directDiffuse += irradiance * BRDF_Lambert( material.diffuseColor );
}
void RE_IndirectDiffuse_Lambert( const in vec3 irradiance, const in vec3 geometryPosition, const in vec3 geometryNormal, const in vec3 geometryViewDir, const in vec3 geometryClearcoatNormal, const in LambertMaterial material, inout ReflectedLight reflectedLight ) {
	reflectedLight.indirectDiffuse += irradiance * BRDF_Lambert( material.diffuseColor );
}
#define RE_Direct				RE_Direct_Lambert
#define RE_IndirectDiffuse		RE_IndirectDiffuse_Lambert`,Vg=`uniform bool receiveShadow;
uniform vec3 ambientLightColor;
#if defined( USE_LIGHT_PROBES )
	uniform vec3 lightProbe[ 9 ];
#endif
vec3 shGetIrradianceAt( in vec3 normal, in vec3 shCoefficients[ 9 ] ) {
	float x = normal.x, y = normal.y, z = normal.z;
	vec3 result = shCoefficients[ 0 ] * 0.886227;
	result += shCoefficients[ 1 ] * 2.0 * 0.511664 * y;
	result += shCoefficients[ 2 ] * 2.0 * 0.511664 * z;
	result += shCoefficients[ 3 ] * 2.0 * 0.511664 * x;
	result += shCoefficients[ 4 ] * 2.0 * 0.429043 * x * y;
	result += shCoefficients[ 5 ] * 2.0 * 0.429043 * y * z;
	result += shCoefficients[ 6 ] * ( 0.743125 * z * z - 0.247708 );
	result += shCoefficients[ 7 ] * 2.0 * 0.429043 * x * z;
	result += shCoefficients[ 8 ] * 0.429043 * ( x * x - y * y );
	return result;
}
vec3 getLightProbeIrradiance( const in vec3 lightProbe[ 9 ], const in vec3 normal ) {
	vec3 worldNormal = inverseTransformDirection( normal, viewMatrix );
	vec3 irradiance = shGetIrradianceAt( worldNormal, lightProbe );
	return irradiance;
}
vec3 getAmbientLightIrradiance( const in vec3 ambientLightColor ) {
	vec3 irradiance = ambientLightColor;
	return irradiance;
}
float getDistanceAttenuation( const in float lightDistance, const in float cutoffDistance, const in float decayExponent ) {
	float distanceFalloff = 1.0 / max( pow( lightDistance, decayExponent ), 0.01 );
	if ( cutoffDistance > 0.0 ) {
		distanceFalloff *= pow2( saturate( 1.0 - pow4( lightDistance / cutoffDistance ) ) );
	}
	return distanceFalloff;
}
float getSpotAttenuation( const in float coneCosine, const in float penumbraCosine, const in float angleCosine ) {
	return smoothstep( coneCosine, penumbraCosine, angleCosine );
}
#if NUM_DIR_LIGHTS > 0
	struct DirectionalLight {
		vec3 direction;
		vec3 color;
	};
	uniform DirectionalLight directionalLights[ NUM_DIR_LIGHTS ];
	void getDirectionalLightInfo( const in DirectionalLight directionalLight, out IncidentLight light ) {
		light.color = directionalLight.color;
		light.direction = directionalLight.direction;
		light.visible = true;
	}
#endif
#if NUM_POINT_LIGHTS > 0
	struct PointLight {
		vec3 position;
		vec3 color;
		float distance;
		float decay;
	};
	uniform PointLight pointLights[ NUM_POINT_LIGHTS ];
	void getPointLightInfo( const in PointLight pointLight, const in vec3 geometryPosition, out IncidentLight light ) {
		vec3 lVector = pointLight.position - geometryPosition;
		light.direction = normalize( lVector );
		float lightDistance = length( lVector );
		light.color = pointLight.color;
		light.color *= getDistanceAttenuation( lightDistance, pointLight.distance, pointLight.decay );
		light.visible = ( light.color != vec3( 0.0 ) );
	}
#endif
#if NUM_SPOT_LIGHTS > 0
	struct SpotLight {
		vec3 position;
		vec3 direction;
		vec3 color;
		float distance;
		float decay;
		float coneCos;
		float penumbraCos;
	};
	uniform SpotLight spotLights[ NUM_SPOT_LIGHTS ];
	void getSpotLightInfo( const in SpotLight spotLight, const in vec3 geometryPosition, out IncidentLight light ) {
		vec3 lVector = spotLight.position - geometryPosition;
		light.direction = normalize( lVector );
		float angleCos = dot( light.direction, spotLight.direction );
		float spotAttenuation = getSpotAttenuation( spotLight.coneCos, spotLight.penumbraCos, angleCos );
		if ( spotAttenuation > 0.0 ) {
			float lightDistance = length( lVector );
			light.color = spotLight.color * spotAttenuation;
			light.color *= getDistanceAttenuation( lightDistance, spotLight.distance, spotLight.decay );
			light.visible = ( light.color != vec3( 0.0 ) );
		} else {
			light.color = vec3( 0.0 );
			light.visible = false;
		}
	}
#endif
#if NUM_RECT_AREA_LIGHTS > 0
	struct RectAreaLight {
		vec3 color;
		vec3 position;
		vec3 halfWidth;
		vec3 halfHeight;
	};
	uniform sampler2D ltc_1;	uniform sampler2D ltc_2;
	uniform RectAreaLight rectAreaLights[ NUM_RECT_AREA_LIGHTS ];
#endif
#if NUM_HEMI_LIGHTS > 0
	struct HemisphereLight {
		vec3 direction;
		vec3 skyColor;
		vec3 groundColor;
	};
	uniform HemisphereLight hemisphereLights[ NUM_HEMI_LIGHTS ];
	vec3 getHemisphereLightIrradiance( const in HemisphereLight hemiLight, const in vec3 normal ) {
		float dotNL = dot( normal, hemiLight.direction );
		float hemiDiffuseWeight = 0.5 * dotNL + 0.5;
		vec3 irradiance = mix( hemiLight.groundColor, hemiLight.skyColor, hemiDiffuseWeight );
		return irradiance;
	}
#endif`,zg=`#ifdef USE_ENVMAP
	vec3 getIBLIrradiance( const in vec3 normal ) {
		#ifdef ENVMAP_TYPE_CUBE_UV
			vec3 worldNormal = inverseTransformDirection( normal, viewMatrix );
			vec4 envMapColor = textureCubeUV( envMap, envMapRotation * worldNormal, 1.0 );
			return PI * envMapColor.rgb * envMapIntensity;
		#else
			return vec3( 0.0 );
		#endif
	}
	vec3 getIBLRadiance( const in vec3 viewDir, const in vec3 normal, const in float roughness ) {
		#ifdef ENVMAP_TYPE_CUBE_UV
			vec3 reflectVec = reflect( - viewDir, normal );
			reflectVec = normalize( mix( reflectVec, normal, roughness * roughness) );
			reflectVec = inverseTransformDirection( reflectVec, viewMatrix );
			vec4 envMapColor = textureCubeUV( envMap, envMapRotation * reflectVec, roughness );
			return envMapColor.rgb * envMapIntensity;
		#else
			return vec3( 0.0 );
		#endif
	}
	#ifdef USE_ANISOTROPY
		vec3 getIBLAnisotropyRadiance( const in vec3 viewDir, const in vec3 normal, const in float roughness, const in vec3 bitangent, const in float anisotropy ) {
			#ifdef ENVMAP_TYPE_CUBE_UV
				vec3 bentNormal = cross( bitangent, viewDir );
				bentNormal = normalize( cross( bentNormal, bitangent ) );
				bentNormal = normalize( mix( bentNormal, normal, pow2( pow2( 1.0 - anisotropy * ( 1.0 - roughness ) ) ) ) );
				return getIBLRadiance( viewDir, bentNormal, roughness );
			#else
				return vec3( 0.0 );
			#endif
		}
	#endif
#endif`,kg=`ToonMaterial material;
material.diffuseColor = diffuseColor.rgb;`,Hg=`varying vec3 vViewPosition;
struct ToonMaterial {
	vec3 diffuseColor;
};
void RE_Direct_Toon( const in IncidentLight directLight, const in vec3 geometryPosition, const in vec3 geometryNormal, const in vec3 geometryViewDir, const in vec3 geometryClearcoatNormal, const in ToonMaterial material, inout ReflectedLight reflectedLight ) {
	vec3 irradiance = getGradientIrradiance( geometryNormal, directLight.direction ) * directLight.color;
	reflectedLight.directDiffuse += irradiance * BRDF_Lambert( material.diffuseColor );
}
void RE_IndirectDiffuse_Toon( const in vec3 irradiance, const in vec3 geometryPosition, const in vec3 geometryNormal, const in vec3 geometryViewDir, const in vec3 geometryClearcoatNormal, const in ToonMaterial material, inout ReflectedLight reflectedLight ) {
	reflectedLight.indirectDiffuse += irradiance * BRDF_Lambert( material.diffuseColor );
}
#define RE_Direct				RE_Direct_Toon
#define RE_IndirectDiffuse		RE_IndirectDiffuse_Toon`,Gg=`BlinnPhongMaterial material;
material.diffuseColor = diffuseColor.rgb;
material.specularColor = specular;
material.specularShininess = shininess;
material.specularStrength = specularStrength;`,Wg=`varying vec3 vViewPosition;
struct BlinnPhongMaterial {
	vec3 diffuseColor;
	vec3 specularColor;
	float specularShininess;
	float specularStrength;
};
void RE_Direct_BlinnPhong( const in IncidentLight directLight, const in vec3 geometryPosition, const in vec3 geometryNormal, const in vec3 geometryViewDir, const in vec3 geometryClearcoatNormal, const in BlinnPhongMaterial material, inout ReflectedLight reflectedLight ) {
	float dotNL = saturate( dot( geometryNormal, directLight.direction ) );
	vec3 irradiance = dotNL * directLight.color;
	reflectedLight.directDiffuse += irradiance * BRDF_Lambert( material.diffuseColor );
	reflectedLight.directSpecular += irradiance * BRDF_BlinnPhong( directLight.direction, geometryViewDir, geometryNormal, material.specularColor, material.specularShininess ) * material.specularStrength;
}
void RE_IndirectDiffuse_BlinnPhong( const in vec3 irradiance, const in vec3 geometryPosition, const in vec3 geometryNormal, const in vec3 geometryViewDir, const in vec3 geometryClearcoatNormal, const in BlinnPhongMaterial material, inout ReflectedLight reflectedLight ) {
	reflectedLight.indirectDiffuse += irradiance * BRDF_Lambert( material.diffuseColor );
}
#define RE_Direct				RE_Direct_BlinnPhong
#define RE_IndirectDiffuse		RE_IndirectDiffuse_BlinnPhong`,Xg=`PhysicalMaterial material;
material.diffuseColor = diffuseColor.rgb * ( 1.0 - metalnessFactor );
vec3 dxy = max( abs( dFdx( nonPerturbedNormal ) ), abs( dFdy( nonPerturbedNormal ) ) );
float geometryRoughness = max( max( dxy.x, dxy.y ), dxy.z );
material.roughness = max( roughnessFactor, 0.0525 );material.roughness += geometryRoughness;
material.roughness = min( material.roughness, 1.0 );
#ifdef IOR
	material.ior = ior;
	#ifdef USE_SPECULAR
		float specularIntensityFactor = specularIntensity;
		vec3 specularColorFactor = specularColor;
		#ifdef USE_SPECULAR_COLORMAP
			specularColorFactor *= texture2D( specularColorMap, vSpecularColorMapUv ).rgb;
		#endif
		#ifdef USE_SPECULAR_INTENSITYMAP
			specularIntensityFactor *= texture2D( specularIntensityMap, vSpecularIntensityMapUv ).a;
		#endif
		material.specularF90 = mix( specularIntensityFactor, 1.0, metalnessFactor );
	#else
		float specularIntensityFactor = 1.0;
		vec3 specularColorFactor = vec3( 1.0 );
		material.specularF90 = 1.0;
	#endif
	material.specularColor = mix( min( pow2( ( material.ior - 1.0 ) / ( material.ior + 1.0 ) ) * specularColorFactor, vec3( 1.0 ) ) * specularIntensityFactor, diffuseColor.rgb, metalnessFactor );
#else
	material.specularColor = mix( vec3( 0.04 ), diffuseColor.rgb, metalnessFactor );
	material.specularF90 = 1.0;
#endif
#ifdef USE_CLEARCOAT
	material.clearcoat = clearcoat;
	material.clearcoatRoughness = clearcoatRoughness;
	material.clearcoatF0 = vec3( 0.04 );
	material.clearcoatF90 = 1.0;
	#ifdef USE_CLEARCOATMAP
		material.clearcoat *= texture2D( clearcoatMap, vClearcoatMapUv ).x;
	#endif
	#ifdef USE_CLEARCOAT_ROUGHNESSMAP
		material.clearcoatRoughness *= texture2D( clearcoatRoughnessMap, vClearcoatRoughnessMapUv ).y;
	#endif
	material.clearcoat = saturate( material.clearcoat );	material.clearcoatRoughness = max( material.clearcoatRoughness, 0.0525 );
	material.clearcoatRoughness += geometryRoughness;
	material.clearcoatRoughness = min( material.clearcoatRoughness, 1.0 );
#endif
#ifdef USE_DISPERSION
	material.dispersion = dispersion;
#endif
#ifdef USE_IRIDESCENCE
	material.iridescence = iridescence;
	material.iridescenceIOR = iridescenceIOR;
	#ifdef USE_IRIDESCENCEMAP
		material.iridescence *= texture2D( iridescenceMap, vIridescenceMapUv ).r;
	#endif
	#ifdef USE_IRIDESCENCE_THICKNESSMAP
		material.iridescenceThickness = (iridescenceThicknessMaximum - iridescenceThicknessMinimum) * texture2D( iridescenceThicknessMap, vIridescenceThicknessMapUv ).g + iridescenceThicknessMinimum;
	#else
		material.iridescenceThickness = iridescenceThicknessMaximum;
	#endif
#endif
#ifdef USE_SHEEN
	material.sheenColor = sheenColor;
	#ifdef USE_SHEEN_COLORMAP
		material.sheenColor *= texture2D( sheenColorMap, vSheenColorMapUv ).rgb;
	#endif
	material.sheenRoughness = clamp( sheenRoughness, 0.07, 1.0 );
	#ifdef USE_SHEEN_ROUGHNESSMAP
		material.sheenRoughness *= texture2D( sheenRoughnessMap, vSheenRoughnessMapUv ).a;
	#endif
#endif
#ifdef USE_ANISOTROPY
	#ifdef USE_ANISOTROPYMAP
		mat2 anisotropyMat = mat2( anisotropyVector.x, anisotropyVector.y, - anisotropyVector.y, anisotropyVector.x );
		vec3 anisotropyPolar = texture2D( anisotropyMap, vAnisotropyMapUv ).rgb;
		vec2 anisotropyV = anisotropyMat * normalize( 2.0 * anisotropyPolar.rg - vec2( 1.0 ) ) * anisotropyPolar.b;
	#else
		vec2 anisotropyV = anisotropyVector;
	#endif
	material.anisotropy = length( anisotropyV );
	if( material.anisotropy == 0.0 ) {
		anisotropyV = vec2( 1.0, 0.0 );
	} else {
		anisotropyV /= material.anisotropy;
		material.anisotropy = saturate( material.anisotropy );
	}
	material.alphaT = mix( pow2( material.roughness ), 1.0, pow2( material.anisotropy ) );
	material.anisotropyT = tbn[ 0 ] * anisotropyV.x + tbn[ 1 ] * anisotropyV.y;
	material.anisotropyB = tbn[ 1 ] * anisotropyV.x - tbn[ 0 ] * anisotropyV.y;
#endif`,qg=`struct PhysicalMaterial {
	vec3 diffuseColor;
	float roughness;
	vec3 specularColor;
	float specularF90;
	float dispersion;
	#ifdef USE_CLEARCOAT
		float clearcoat;
		float clearcoatRoughness;
		vec3 clearcoatF0;
		float clearcoatF90;
	#endif
	#ifdef USE_IRIDESCENCE
		float iridescence;
		float iridescenceIOR;
		float iridescenceThickness;
		vec3 iridescenceFresnel;
		vec3 iridescenceF0;
	#endif
	#ifdef USE_SHEEN
		vec3 sheenColor;
		float sheenRoughness;
	#endif
	#ifdef IOR
		float ior;
	#endif
	#ifdef USE_TRANSMISSION
		float transmission;
		float transmissionAlpha;
		float thickness;
		float attenuationDistance;
		vec3 attenuationColor;
	#endif
	#ifdef USE_ANISOTROPY
		float anisotropy;
		float alphaT;
		vec3 anisotropyT;
		vec3 anisotropyB;
	#endif
};
vec3 clearcoatSpecularDirect = vec3( 0.0 );
vec3 clearcoatSpecularIndirect = vec3( 0.0 );
vec3 sheenSpecularDirect = vec3( 0.0 );
vec3 sheenSpecularIndirect = vec3(0.0 );
vec3 Schlick_to_F0( const in vec3 f, const in float f90, const in float dotVH ) {
    float x = clamp( 1.0 - dotVH, 0.0, 1.0 );
    float x2 = x * x;
    float x5 = clamp( x * x2 * x2, 0.0, 0.9999 );
    return ( f - vec3( f90 ) * x5 ) / ( 1.0 - x5 );
}
float V_GGX_SmithCorrelated( const in float alpha, const in float dotNL, const in float dotNV ) {
	float a2 = pow2( alpha );
	float gv = dotNL * sqrt( a2 + ( 1.0 - a2 ) * pow2( dotNV ) );
	float gl = dotNV * sqrt( a2 + ( 1.0 - a2 ) * pow2( dotNL ) );
	return 0.5 / max( gv + gl, EPSILON );
}
float D_GGX( const in float alpha, const in float dotNH ) {
	float a2 = pow2( alpha );
	float denom = pow2( dotNH ) * ( a2 - 1.0 ) + 1.0;
	return RECIPROCAL_PI * a2 / pow2( denom );
}
#ifdef USE_ANISOTROPY
	float V_GGX_SmithCorrelated_Anisotropic( const in float alphaT, const in float alphaB, const in float dotTV, const in float dotBV, const in float dotTL, const in float dotBL, const in float dotNV, const in float dotNL ) {
		float gv = dotNL * length( vec3( alphaT * dotTV, alphaB * dotBV, dotNV ) );
		float gl = dotNV * length( vec3( alphaT * dotTL, alphaB * dotBL, dotNL ) );
		float v = 0.5 / ( gv + gl );
		return saturate(v);
	}
	float D_GGX_Anisotropic( const in float alphaT, const in float alphaB, const in float dotNH, const in float dotTH, const in float dotBH ) {
		float a2 = alphaT * alphaB;
		highp vec3 v = vec3( alphaB * dotTH, alphaT * dotBH, a2 * dotNH );
		highp float v2 = dot( v, v );
		float w2 = a2 / v2;
		return RECIPROCAL_PI * a2 * pow2 ( w2 );
	}
#endif
#ifdef USE_CLEARCOAT
	vec3 BRDF_GGX_Clearcoat( const in vec3 lightDir, const in vec3 viewDir, const in vec3 normal, const in PhysicalMaterial material) {
		vec3 f0 = material.clearcoatF0;
		float f90 = material.clearcoatF90;
		float roughness = material.clearcoatRoughness;
		float alpha = pow2( roughness );
		vec3 halfDir = normalize( lightDir + viewDir );
		float dotNL = saturate( dot( normal, lightDir ) );
		float dotNV = saturate( dot( normal, viewDir ) );
		float dotNH = saturate( dot( normal, halfDir ) );
		float dotVH = saturate( dot( viewDir, halfDir ) );
		vec3 F = F_Schlick( f0, f90, dotVH );
		float V = V_GGX_SmithCorrelated( alpha, dotNL, dotNV );
		float D = D_GGX( alpha, dotNH );
		return F * ( V * D );
	}
#endif
vec3 BRDF_GGX( const in vec3 lightDir, const in vec3 viewDir, const in vec3 normal, const in PhysicalMaterial material ) {
	vec3 f0 = material.specularColor;
	float f90 = material.specularF90;
	float roughness = material.roughness;
	float alpha = pow2( roughness );
	vec3 halfDir = normalize( lightDir + viewDir );
	float dotNL = saturate( dot( normal, lightDir ) );
	float dotNV = saturate( dot( normal, viewDir ) );
	float dotNH = saturate( dot( normal, halfDir ) );
	float dotVH = saturate( dot( viewDir, halfDir ) );
	vec3 F = F_Schlick( f0, f90, dotVH );
	#ifdef USE_IRIDESCENCE
		F = mix( F, material.iridescenceFresnel, material.iridescence );
	#endif
	#ifdef USE_ANISOTROPY
		float dotTL = dot( material.anisotropyT, lightDir );
		float dotTV = dot( material.anisotropyT, viewDir );
		float dotTH = dot( material.anisotropyT, halfDir );
		float dotBL = dot( material.anisotropyB, lightDir );
		float dotBV = dot( material.anisotropyB, viewDir );
		float dotBH = dot( material.anisotropyB, halfDir );
		float V = V_GGX_SmithCorrelated_Anisotropic( material.alphaT, alpha, dotTV, dotBV, dotTL, dotBL, dotNV, dotNL );
		float D = D_GGX_Anisotropic( material.alphaT, alpha, dotNH, dotTH, dotBH );
	#else
		float V = V_GGX_SmithCorrelated( alpha, dotNL, dotNV );
		float D = D_GGX( alpha, dotNH );
	#endif
	return F * ( V * D );
}
vec2 LTC_Uv( const in vec3 N, const in vec3 V, const in float roughness ) {
	const float LUT_SIZE = 64.0;
	const float LUT_SCALE = ( LUT_SIZE - 1.0 ) / LUT_SIZE;
	const float LUT_BIAS = 0.5 / LUT_SIZE;
	float dotNV = saturate( dot( N, V ) );
	vec2 uv = vec2( roughness, sqrt( 1.0 - dotNV ) );
	uv = uv * LUT_SCALE + LUT_BIAS;
	return uv;
}
float LTC_ClippedSphereFormFactor( const in vec3 f ) {
	float l = length( f );
	return max( ( l * l + f.z ) / ( l + 1.0 ), 0.0 );
}
vec3 LTC_EdgeVectorFormFactor( const in vec3 v1, const in vec3 v2 ) {
	float x = dot( v1, v2 );
	float y = abs( x );
	float a = 0.8543985 + ( 0.4965155 + 0.0145206 * y ) * y;
	float b = 3.4175940 + ( 4.1616724 + y ) * y;
	float v = a / b;
	float theta_sintheta = ( x > 0.0 ) ? v : 0.5 * inversesqrt( max( 1.0 - x * x, 1e-7 ) ) - v;
	return cross( v1, v2 ) * theta_sintheta;
}
vec3 LTC_Evaluate( const in vec3 N, const in vec3 V, const in vec3 P, const in mat3 mInv, const in vec3 rectCoords[ 4 ] ) {
	vec3 v1 = rectCoords[ 1 ] - rectCoords[ 0 ];
	vec3 v2 = rectCoords[ 3 ] - rectCoords[ 0 ];
	vec3 lightNormal = cross( v1, v2 );
	if( dot( lightNormal, P - rectCoords[ 0 ] ) < 0.0 ) return vec3( 0.0 );
	vec3 T1, T2;
	T1 = normalize( V - N * dot( V, N ) );
	T2 = - cross( N, T1 );
	mat3 mat = mInv * transposeMat3( mat3( T1, T2, N ) );
	vec3 coords[ 4 ];
	coords[ 0 ] = mat * ( rectCoords[ 0 ] - P );
	coords[ 1 ] = mat * ( rectCoords[ 1 ] - P );
	coords[ 2 ] = mat * ( rectCoords[ 2 ] - P );
	coords[ 3 ] = mat * ( rectCoords[ 3 ] - P );
	coords[ 0 ] = normalize( coords[ 0 ] );
	coords[ 1 ] = normalize( coords[ 1 ] );
	coords[ 2 ] = normalize( coords[ 2 ] );
	coords[ 3 ] = normalize( coords[ 3 ] );
	vec3 vectorFormFactor = vec3( 0.0 );
	vectorFormFactor += LTC_EdgeVectorFormFactor( coords[ 0 ], coords[ 1 ] );
	vectorFormFactor += LTC_EdgeVectorFormFactor( coords[ 1 ], coords[ 2 ] );
	vectorFormFactor += LTC_EdgeVectorFormFactor( coords[ 2 ], coords[ 3 ] );
	vectorFormFactor += LTC_EdgeVectorFormFactor( coords[ 3 ], coords[ 0 ] );
	float result = LTC_ClippedSphereFormFactor( vectorFormFactor );
	return vec3( result );
}
#if defined( USE_SHEEN )
float D_Charlie( float roughness, float dotNH ) {
	float alpha = pow2( roughness );
	float invAlpha = 1.0 / alpha;
	float cos2h = dotNH * dotNH;
	float sin2h = max( 1.0 - cos2h, 0.0078125 );
	return ( 2.0 + invAlpha ) * pow( sin2h, invAlpha * 0.5 ) / ( 2.0 * PI );
}
float V_Neubelt( float dotNV, float dotNL ) {
	return saturate( 1.0 / ( 4.0 * ( dotNL + dotNV - dotNL * dotNV ) ) );
}
vec3 BRDF_Sheen( const in vec3 lightDir, const in vec3 viewDir, const in vec3 normal, vec3 sheenColor, const in float sheenRoughness ) {
	vec3 halfDir = normalize( lightDir + viewDir );
	float dotNL = saturate( dot( normal, lightDir ) );
	float dotNV = saturate( dot( normal, viewDir ) );
	float dotNH = saturate( dot( normal, halfDir ) );
	float D = D_Charlie( sheenRoughness, dotNH );
	float V = V_Neubelt( dotNV, dotNL );
	return sheenColor * ( D * V );
}
#endif
float IBLSheenBRDF( const in vec3 normal, const in vec3 viewDir, const in float roughness ) {
	float dotNV = saturate( dot( normal, viewDir ) );
	float r2 = roughness * roughness;
	float a = roughness < 0.25 ? -339.2 * r2 + 161.4 * roughness - 25.9 : -8.48 * r2 + 14.3 * roughness - 9.95;
	float b = roughness < 0.25 ? 44.0 * r2 - 23.7 * roughness + 3.26 : 1.97 * r2 - 3.27 * roughness + 0.72;
	float DG = exp( a * dotNV + b ) + ( roughness < 0.25 ? 0.0 : 0.1 * ( roughness - 0.25 ) );
	return saturate( DG * RECIPROCAL_PI );
}
vec2 DFGApprox( const in vec3 normal, const in vec3 viewDir, const in float roughness ) {
	float dotNV = saturate( dot( normal, viewDir ) );
	const vec4 c0 = vec4( - 1, - 0.0275, - 0.572, 0.022 );
	const vec4 c1 = vec4( 1, 0.0425, 1.04, - 0.04 );
	vec4 r = roughness * c0 + c1;
	float a004 = min( r.x * r.x, exp2( - 9.28 * dotNV ) ) * r.x + r.y;
	vec2 fab = vec2( - 1.04, 1.04 ) * a004 + r.zw;
	return fab;
}
vec3 EnvironmentBRDF( const in vec3 normal, const in vec3 viewDir, const in vec3 specularColor, const in float specularF90, const in float roughness ) {
	vec2 fab = DFGApprox( normal, viewDir, roughness );
	return specularColor * fab.x + specularF90 * fab.y;
}
#ifdef USE_IRIDESCENCE
void computeMultiscatteringIridescence( const in vec3 normal, const in vec3 viewDir, const in vec3 specularColor, const in float specularF90, const in float iridescence, const in vec3 iridescenceF0, const in float roughness, inout vec3 singleScatter, inout vec3 multiScatter ) {
#else
void computeMultiscattering( const in vec3 normal, const in vec3 viewDir, const in vec3 specularColor, const in float specularF90, const in float roughness, inout vec3 singleScatter, inout vec3 multiScatter ) {
#endif
	vec2 fab = DFGApprox( normal, viewDir, roughness );
	#ifdef USE_IRIDESCENCE
		vec3 Fr = mix( specularColor, iridescenceF0, iridescence );
	#else
		vec3 Fr = specularColor;
	#endif
	vec3 FssEss = Fr * fab.x + specularF90 * fab.y;
	float Ess = fab.x + fab.y;
	float Ems = 1.0 - Ess;
	vec3 Favg = Fr + ( 1.0 - Fr ) * 0.047619;	vec3 Fms = FssEss * Favg / ( 1.0 - Ems * Favg );
	singleScatter += FssEss;
	multiScatter += Fms * Ems;
}
#if NUM_RECT_AREA_LIGHTS > 0
	void RE_Direct_RectArea_Physical( const in RectAreaLight rectAreaLight, const in vec3 geometryPosition, const in vec3 geometryNormal, const in vec3 geometryViewDir, const in vec3 geometryClearcoatNormal, const in PhysicalMaterial material, inout ReflectedLight reflectedLight ) {
		vec3 normal = geometryNormal;
		vec3 viewDir = geometryViewDir;
		vec3 position = geometryPosition;
		vec3 lightPos = rectAreaLight.position;
		vec3 halfWidth = rectAreaLight.halfWidth;
		vec3 halfHeight = rectAreaLight.halfHeight;
		vec3 lightColor = rectAreaLight.color;
		float roughness = material.roughness;
		vec3 rectCoords[ 4 ];
		rectCoords[ 0 ] = lightPos + halfWidth - halfHeight;		rectCoords[ 1 ] = lightPos - halfWidth - halfHeight;
		rectCoords[ 2 ] = lightPos - halfWidth + halfHeight;
		rectCoords[ 3 ] = lightPos + halfWidth + halfHeight;
		vec2 uv = LTC_Uv( normal, viewDir, roughness );
		vec4 t1 = texture2D( ltc_1, uv );
		vec4 t2 = texture2D( ltc_2, uv );
		mat3 mInv = mat3(
			vec3( t1.x, 0, t1.y ),
			vec3(    0, 1,    0 ),
			vec3( t1.z, 0, t1.w )
		);
		vec3 fresnel = ( material.specularColor * t2.x + ( vec3( 1.0 ) - material.specularColor ) * t2.y );
		reflectedLight.directSpecular += lightColor * fresnel * LTC_Evaluate( normal, viewDir, position, mInv, rectCoords );
		reflectedLight.directDiffuse += lightColor * material.diffuseColor * LTC_Evaluate( normal, viewDir, position, mat3( 1.0 ), rectCoords );
	}
#endif
void RE_Direct_Physical( const in IncidentLight directLight, const in vec3 geometryPosition, const in vec3 geometryNormal, const in vec3 geometryViewDir, const in vec3 geometryClearcoatNormal, const in PhysicalMaterial material, inout ReflectedLight reflectedLight ) {
	float dotNL = saturate( dot( geometryNormal, directLight.direction ) );
	vec3 irradiance = dotNL * directLight.color;
	#ifdef USE_CLEARCOAT
		float dotNLcc = saturate( dot( geometryClearcoatNormal, directLight.direction ) );
		vec3 ccIrradiance = dotNLcc * directLight.color;
		clearcoatSpecularDirect += ccIrradiance * BRDF_GGX_Clearcoat( directLight.direction, geometryViewDir, geometryClearcoatNormal, material );
	#endif
	#ifdef USE_SHEEN
		sheenSpecularDirect += irradiance * BRDF_Sheen( directLight.direction, geometryViewDir, geometryNormal, material.sheenColor, material.sheenRoughness );
	#endif
	reflectedLight.directSpecular += irradiance * BRDF_GGX( directLight.direction, geometryViewDir, geometryNormal, material );
	reflectedLight.directDiffuse += irradiance * BRDF_Lambert( material.diffuseColor );
}
void RE_IndirectDiffuse_Physical( const in vec3 irradiance, const in vec3 geometryPosition, const in vec3 geometryNormal, const in vec3 geometryViewDir, const in vec3 geometryClearcoatNormal, const in PhysicalMaterial material, inout ReflectedLight reflectedLight ) {
	reflectedLight.indirectDiffuse += irradiance * BRDF_Lambert( material.diffuseColor );
}
void RE_IndirectSpecular_Physical( const in vec3 radiance, const in vec3 irradiance, const in vec3 clearcoatRadiance, const in vec3 geometryPosition, const in vec3 geometryNormal, const in vec3 geometryViewDir, const in vec3 geometryClearcoatNormal, const in PhysicalMaterial material, inout ReflectedLight reflectedLight) {
	#ifdef USE_CLEARCOAT
		clearcoatSpecularIndirect += clearcoatRadiance * EnvironmentBRDF( geometryClearcoatNormal, geometryViewDir, material.clearcoatF0, material.clearcoatF90, material.clearcoatRoughness );
	#endif
	#ifdef USE_SHEEN
		sheenSpecularIndirect += irradiance * material.sheenColor * IBLSheenBRDF( geometryNormal, geometryViewDir, material.sheenRoughness );
	#endif
	vec3 singleScattering = vec3( 0.0 );
	vec3 multiScattering = vec3( 0.0 );
	vec3 cosineWeightedIrradiance = irradiance * RECIPROCAL_PI;
	#ifdef USE_IRIDESCENCE
		computeMultiscatteringIridescence( geometryNormal, geometryViewDir, material.specularColor, material.specularF90, material.iridescence, material.iridescenceFresnel, material.roughness, singleScattering, multiScattering );
	#else
		computeMultiscattering( geometryNormal, geometryViewDir, material.specularColor, material.specularF90, material.roughness, singleScattering, multiScattering );
	#endif
	vec3 totalScattering = singleScattering + multiScattering;
	vec3 diffuse = material.diffuseColor * ( 1.0 - max( max( totalScattering.r, totalScattering.g ), totalScattering.b ) );
	reflectedLight.indirectSpecular += radiance * singleScattering;
	reflectedLight.indirectSpecular += multiScattering * cosineWeightedIrradiance;
	reflectedLight.indirectDiffuse += diffuse * cosineWeightedIrradiance;
}
#define RE_Direct				RE_Direct_Physical
#define RE_Direct_RectArea		RE_Direct_RectArea_Physical
#define RE_IndirectDiffuse		RE_IndirectDiffuse_Physical
#define RE_IndirectSpecular		RE_IndirectSpecular_Physical
float computeSpecularOcclusion( const in float dotNV, const in float ambientOcclusion, const in float roughness ) {
	return saturate( pow( dotNV + ambientOcclusion, exp2( - 16.0 * roughness - 1.0 ) ) - 1.0 + ambientOcclusion );
}`,Yg=`
vec3 geometryPosition = - vViewPosition;
vec3 geometryNormal = normal;
vec3 geometryViewDir = ( isOrthographic ) ? vec3( 0, 0, 1 ) : normalize( vViewPosition );
vec3 geometryClearcoatNormal = vec3( 0.0 );
#ifdef USE_CLEARCOAT
	geometryClearcoatNormal = clearcoatNormal;
#endif
#ifdef USE_IRIDESCENCE
	float dotNVi = saturate( dot( normal, geometryViewDir ) );
	if ( material.iridescenceThickness == 0.0 ) {
		material.iridescence = 0.0;
	} else {
		material.iridescence = saturate( material.iridescence );
	}
	if ( material.iridescence > 0.0 ) {
		material.iridescenceFresnel = evalIridescence( 1.0, material.iridescenceIOR, dotNVi, material.iridescenceThickness, material.specularColor );
		material.iridescenceF0 = Schlick_to_F0( material.iridescenceFresnel, 1.0, dotNVi );
	}
#endif
IncidentLight directLight;
#if ( NUM_POINT_LIGHTS > 0 ) && defined( RE_Direct )
	PointLight pointLight;
	#if defined( USE_SHADOWMAP ) && NUM_POINT_LIGHT_SHADOWS > 0
	PointLightShadow pointLightShadow;
	#endif
	#pragma unroll_loop_start
	for ( int i = 0; i < NUM_POINT_LIGHTS; i ++ ) {
		pointLight = pointLights[ i ];
		getPointLightInfo( pointLight, geometryPosition, directLight );
		#if defined( USE_SHADOWMAP ) && ( UNROLLED_LOOP_INDEX < NUM_POINT_LIGHT_SHADOWS )
		pointLightShadow = pointLightShadows[ i ];
		directLight.color *= ( directLight.visible && receiveShadow ) ? getPointShadow( pointShadowMap[ i ], pointLightShadow.shadowMapSize, pointLightShadow.shadowIntensity, pointLightShadow.shadowBias, pointLightShadow.shadowRadius, vPointShadowCoord[ i ], pointLightShadow.shadowCameraNear, pointLightShadow.shadowCameraFar ) : 1.0;
		#endif
		RE_Direct( directLight, geometryPosition, geometryNormal, geometryViewDir, geometryClearcoatNormal, material, reflectedLight );
	}
	#pragma unroll_loop_end
#endif
#if ( NUM_SPOT_LIGHTS > 0 ) && defined( RE_Direct )
	SpotLight spotLight;
	vec4 spotColor;
	vec3 spotLightCoord;
	bool inSpotLightMap;
	#if defined( USE_SHADOWMAP ) && NUM_SPOT_LIGHT_SHADOWS > 0
	SpotLightShadow spotLightShadow;
	#endif
	#pragma unroll_loop_start
	for ( int i = 0; i < NUM_SPOT_LIGHTS; i ++ ) {
		spotLight = spotLights[ i ];
		getSpotLightInfo( spotLight, geometryPosition, directLight );
		#if ( UNROLLED_LOOP_INDEX < NUM_SPOT_LIGHT_SHADOWS_WITH_MAPS )
		#define SPOT_LIGHT_MAP_INDEX UNROLLED_LOOP_INDEX
		#elif ( UNROLLED_LOOP_INDEX < NUM_SPOT_LIGHT_SHADOWS )
		#define SPOT_LIGHT_MAP_INDEX NUM_SPOT_LIGHT_MAPS
		#else
		#define SPOT_LIGHT_MAP_INDEX ( UNROLLED_LOOP_INDEX - NUM_SPOT_LIGHT_SHADOWS + NUM_SPOT_LIGHT_SHADOWS_WITH_MAPS )
		#endif
		#if ( SPOT_LIGHT_MAP_INDEX < NUM_SPOT_LIGHT_MAPS )
			spotLightCoord = vSpotLightCoord[ i ].xyz / vSpotLightCoord[ i ].w;
			inSpotLightMap = all( lessThan( abs( spotLightCoord * 2. - 1. ), vec3( 1.0 ) ) );
			spotColor = texture2D( spotLightMap[ SPOT_LIGHT_MAP_INDEX ], spotLightCoord.xy );
			directLight.color = inSpotLightMap ? directLight.color * spotColor.rgb : directLight.color;
		#endif
		#undef SPOT_LIGHT_MAP_INDEX
		#if defined( USE_SHADOWMAP ) && ( UNROLLED_LOOP_INDEX < NUM_SPOT_LIGHT_SHADOWS )
		spotLightShadow = spotLightShadows[ i ];
		directLight.color *= ( directLight.visible && receiveShadow ) ? getShadow( spotShadowMap[ i ], spotLightShadow.shadowMapSize, spotLightShadow.shadowIntensity, spotLightShadow.shadowBias, spotLightShadow.shadowRadius, vSpotLightCoord[ i ] ) : 1.0;
		#endif
		RE_Direct( directLight, geometryPosition, geometryNormal, geometryViewDir, geometryClearcoatNormal, material, reflectedLight );
	}
	#pragma unroll_loop_end
#endif
#if ( NUM_DIR_LIGHTS > 0 ) && defined( RE_Direct )
	DirectionalLight directionalLight;
	#if defined( USE_SHADOWMAP ) && NUM_DIR_LIGHT_SHADOWS > 0
	DirectionalLightShadow directionalLightShadow;
	#endif
	#pragma unroll_loop_start
	for ( int i = 0; i < NUM_DIR_LIGHTS; i ++ ) {
		directionalLight = directionalLights[ i ];
		getDirectionalLightInfo( directionalLight, directLight );
		#if defined( USE_SHADOWMAP ) && ( UNROLLED_LOOP_INDEX < NUM_DIR_LIGHT_SHADOWS )
		directionalLightShadow = directionalLightShadows[ i ];
		directLight.color *= ( directLight.visible && receiveShadow ) ? getShadow( directionalShadowMap[ i ], directionalLightShadow.shadowMapSize, directionalLightShadow.shadowIntensity, directionalLightShadow.shadowBias, directionalLightShadow.shadowRadius, vDirectionalShadowCoord[ i ] ) : 1.0;
		#endif
		RE_Direct( directLight, geometryPosition, geometryNormal, geometryViewDir, geometryClearcoatNormal, material, reflectedLight );
	}
	#pragma unroll_loop_end
#endif
#if ( NUM_RECT_AREA_LIGHTS > 0 ) && defined( RE_Direct_RectArea )
	RectAreaLight rectAreaLight;
	#pragma unroll_loop_start
	for ( int i = 0; i < NUM_RECT_AREA_LIGHTS; i ++ ) {
		rectAreaLight = rectAreaLights[ i ];
		RE_Direct_RectArea( rectAreaLight, geometryPosition, geometryNormal, geometryViewDir, geometryClearcoatNormal, material, reflectedLight );
	}
	#pragma unroll_loop_end
#endif
#if defined( RE_IndirectDiffuse )
	vec3 iblIrradiance = vec3( 0.0 );
	vec3 irradiance = getAmbientLightIrradiance( ambientLightColor );
	#if defined( USE_LIGHT_PROBES )
		irradiance += getLightProbeIrradiance( lightProbe, geometryNormal );
	#endif
	#if ( NUM_HEMI_LIGHTS > 0 )
		#pragma unroll_loop_start
		for ( int i = 0; i < NUM_HEMI_LIGHTS; i ++ ) {
			irradiance += getHemisphereLightIrradiance( hemisphereLights[ i ], geometryNormal );
		}
		#pragma unroll_loop_end
	#endif
#endif
#if defined( RE_IndirectSpecular )
	vec3 radiance = vec3( 0.0 );
	vec3 clearcoatRadiance = vec3( 0.0 );
#endif`,Kg=`#if defined( RE_IndirectDiffuse )
	#ifdef USE_LIGHTMAP
		vec4 lightMapTexel = texture2D( lightMap, vLightMapUv );
		vec3 lightMapIrradiance = lightMapTexel.rgb * lightMapIntensity;
		irradiance += lightMapIrradiance;
	#endif
	#if defined( USE_ENVMAP ) && defined( STANDARD ) && defined( ENVMAP_TYPE_CUBE_UV )
		iblIrradiance += getIBLIrradiance( geometryNormal );
	#endif
#endif
#if defined( USE_ENVMAP ) && defined( RE_IndirectSpecular )
	#ifdef USE_ANISOTROPY
		radiance += getIBLAnisotropyRadiance( geometryViewDir, geometryNormal, material.roughness, material.anisotropyB, material.anisotropy );
	#else
		radiance += getIBLRadiance( geometryViewDir, geometryNormal, material.roughness );
	#endif
	#ifdef USE_CLEARCOAT
		clearcoatRadiance += getIBLRadiance( geometryViewDir, geometryClearcoatNormal, material.clearcoatRoughness );
	#endif
#endif`,$g=`#if defined( RE_IndirectDiffuse )
	RE_IndirectDiffuse( irradiance, geometryPosition, geometryNormal, geometryViewDir, geometryClearcoatNormal, material, reflectedLight );
#endif
#if defined( RE_IndirectSpecular )
	RE_IndirectSpecular( radiance, iblIrradiance, clearcoatRadiance, geometryPosition, geometryNormal, geometryViewDir, geometryClearcoatNormal, material, reflectedLight );
#endif`,Zg=`#if defined( USE_LOGDEPTHBUF )
	gl_FragDepth = vIsPerspective == 0.0 ? gl_FragCoord.z : log2( vFragDepth ) * logDepthBufFC * 0.5;
#endif`,Jg=`#if defined( USE_LOGDEPTHBUF )
	uniform float logDepthBufFC;
	varying float vFragDepth;
	varying float vIsPerspective;
#endif`,Qg=`#ifdef USE_LOGDEPTHBUF
	varying float vFragDepth;
	varying float vIsPerspective;
#endif`,jg=`#ifdef USE_LOGDEPTHBUF
	vFragDepth = 1.0 + gl_Position.w;
	vIsPerspective = float( isPerspectiveMatrix( projectionMatrix ) );
#endif`,t0=`#ifdef USE_MAP
	vec4 sampledDiffuseColor = texture2D( map, vMapUv );
	#ifdef DECODE_VIDEO_TEXTURE
		sampledDiffuseColor = vec4( mix( pow( sampledDiffuseColor.rgb * 0.9478672986 + vec3( 0.0521327014 ), vec3( 2.4 ) ), sampledDiffuseColor.rgb * 0.0773993808, vec3( lessThanEqual( sampledDiffuseColor.rgb, vec3( 0.04045 ) ) ) ), sampledDiffuseColor.w );
	
	#endif
	diffuseColor *= sampledDiffuseColor;
#endif`,e0=`#ifdef USE_MAP
	uniform sampler2D map;
#endif`,n0=`#if defined( USE_MAP ) || defined( USE_ALPHAMAP )
	#if defined( USE_POINTS_UV )
		vec2 uv = vUv;
	#else
		vec2 uv = ( uvTransform * vec3( gl_PointCoord.x, 1.0 - gl_PointCoord.y, 1 ) ).xy;
	#endif
#endif
#ifdef USE_MAP
	diffuseColor *= texture2D( map, uv );
#endif
#ifdef USE_ALPHAMAP
	diffuseColor.a *= texture2D( alphaMap, uv ).g;
#endif`,i0=`#if defined( USE_POINTS_UV )
	varying vec2 vUv;
#else
	#if defined( USE_MAP ) || defined( USE_ALPHAMAP )
		uniform mat3 uvTransform;
	#endif
#endif
#ifdef USE_MAP
	uniform sampler2D map;
#endif
#ifdef USE_ALPHAMAP
	uniform sampler2D alphaMap;
#endif`,s0=`float metalnessFactor = metalness;
#ifdef USE_METALNESSMAP
	vec4 texelMetalness = texture2D( metalnessMap, vMetalnessMapUv );
	metalnessFactor *= texelMetalness.b;
#endif`,r0=`#ifdef USE_METALNESSMAP
	uniform sampler2D metalnessMap;
#endif`,o0=`#ifdef USE_INSTANCING_MORPH
	float morphTargetInfluences[ MORPHTARGETS_COUNT ];
	float morphTargetBaseInfluence = texelFetch( morphTexture, ivec2( 0, gl_InstanceID ), 0 ).r;
	for ( int i = 0; i < MORPHTARGETS_COUNT; i ++ ) {
		morphTargetInfluences[i] =  texelFetch( morphTexture, ivec2( i + 1, gl_InstanceID ), 0 ).r;
	}
#endif`,a0=`#if defined( USE_MORPHCOLORS )
	vColor *= morphTargetBaseInfluence;
	for ( int i = 0; i < MORPHTARGETS_COUNT; i ++ ) {
		#if defined( USE_COLOR_ALPHA )
			if ( morphTargetInfluences[ i ] != 0.0 ) vColor += getMorph( gl_VertexID, i, 2 ) * morphTargetInfluences[ i ];
		#elif defined( USE_COLOR )
			if ( morphTargetInfluences[ i ] != 0.0 ) vColor += getMorph( gl_VertexID, i, 2 ).rgb * morphTargetInfluences[ i ];
		#endif
	}
#endif`,l0=`#ifdef USE_MORPHNORMALS
	objectNormal *= morphTargetBaseInfluence;
	for ( int i = 0; i < MORPHTARGETS_COUNT; i ++ ) {
		if ( morphTargetInfluences[ i ] != 0.0 ) objectNormal += getMorph( gl_VertexID, i, 1 ).xyz * morphTargetInfluences[ i ];
	}
#endif`,c0=`#ifdef USE_MORPHTARGETS
	#ifndef USE_INSTANCING_MORPH
		uniform float morphTargetBaseInfluence;
		uniform float morphTargetInfluences[ MORPHTARGETS_COUNT ];
	#endif
	uniform sampler2DArray morphTargetsTexture;
	uniform ivec2 morphTargetsTextureSize;
	vec4 getMorph( const in int vertexIndex, const in int morphTargetIndex, const in int offset ) {
		int texelIndex = vertexIndex * MORPHTARGETS_TEXTURE_STRIDE + offset;
		int y = texelIndex / morphTargetsTextureSize.x;
		int x = texelIndex - y * morphTargetsTextureSize.x;
		ivec3 morphUV = ivec3( x, y, morphTargetIndex );
		return texelFetch( morphTargetsTexture, morphUV, 0 );
	}
#endif`,h0=`#ifdef USE_MORPHTARGETS
	transformed *= morphTargetBaseInfluence;
	for ( int i = 0; i < MORPHTARGETS_COUNT; i ++ ) {
		if ( morphTargetInfluences[ i ] != 0.0 ) transformed += getMorph( gl_VertexID, i, 0 ).xyz * morphTargetInfluences[ i ];
	}
#endif`,u0=`float faceDirection = gl_FrontFacing ? 1.0 : - 1.0;
#ifdef FLAT_SHADED
	vec3 fdx = dFdx( vViewPosition );
	vec3 fdy = dFdy( vViewPosition );
	vec3 normal = normalize( cross( fdx, fdy ) );
#else
	vec3 normal = normalize( vNormal );
	#ifdef DOUBLE_SIDED
		normal *= faceDirection;
	#endif
#endif
#if defined( USE_NORMALMAP_TANGENTSPACE ) || defined( USE_CLEARCOAT_NORMALMAP ) || defined( USE_ANISOTROPY )
	#ifdef USE_TANGENT
		mat3 tbn = mat3( normalize( vTangent ), normalize( vBitangent ), normal );
	#else
		mat3 tbn = getTangentFrame( - vViewPosition, normal,
		#if defined( USE_NORMALMAP )
			vNormalMapUv
		#elif defined( USE_CLEARCOAT_NORMALMAP )
			vClearcoatNormalMapUv
		#else
			vUv
		#endif
		);
	#endif
	#if defined( DOUBLE_SIDED ) && ! defined( FLAT_SHADED )
		tbn[0] *= faceDirection;
		tbn[1] *= faceDirection;
	#endif
#endif
#ifdef USE_CLEARCOAT_NORMALMAP
	#ifdef USE_TANGENT
		mat3 tbn2 = mat3( normalize( vTangent ), normalize( vBitangent ), normal );
	#else
		mat3 tbn2 = getTangentFrame( - vViewPosition, normal, vClearcoatNormalMapUv );
	#endif
	#if defined( DOUBLE_SIDED ) && ! defined( FLAT_SHADED )
		tbn2[0] *= faceDirection;
		tbn2[1] *= faceDirection;
	#endif
#endif
vec3 nonPerturbedNormal = normal;`,f0=`#ifdef USE_NORMALMAP_OBJECTSPACE
	normal = texture2D( normalMap, vNormalMapUv ).xyz * 2.0 - 1.0;
	#ifdef FLIP_SIDED
		normal = - normal;
	#endif
	#ifdef DOUBLE_SIDED
		normal = normal * faceDirection;
	#endif
	normal = normalize( normalMatrix * normal );
#elif defined( USE_NORMALMAP_TANGENTSPACE )
	vec3 mapN = texture2D( normalMap, vNormalMapUv ).xyz * 2.0 - 1.0;
	mapN.xy *= normalScale;
	normal = normalize( tbn * mapN );
#elif defined( USE_BUMPMAP )
	normal = perturbNormalArb( - vViewPosition, normal, dHdxy_fwd(), faceDirection );
#endif`,d0=`#ifndef FLAT_SHADED
	varying vec3 vNormal;
	#ifdef USE_TANGENT
		varying vec3 vTangent;
		varying vec3 vBitangent;
	#endif
#endif`,p0=`#ifndef FLAT_SHADED
	varying vec3 vNormal;
	#ifdef USE_TANGENT
		varying vec3 vTangent;
		varying vec3 vBitangent;
	#endif
#endif`,m0=`#ifndef FLAT_SHADED
	vNormal = normalize( transformedNormal );
	#ifdef USE_TANGENT
		vTangent = normalize( transformedTangent );
		vBitangent = normalize( cross( vNormal, vTangent ) * tangent.w );
	#endif
#endif`,g0=`#ifdef USE_NORMALMAP
	uniform sampler2D normalMap;
	uniform vec2 normalScale;
#endif
#ifdef USE_NORMALMAP_OBJECTSPACE
	uniform mat3 normalMatrix;
#endif
#if ! defined ( USE_TANGENT ) && ( defined ( USE_NORMALMAP_TANGENTSPACE ) || defined ( USE_CLEARCOAT_NORMALMAP ) || defined( USE_ANISOTROPY ) )
	mat3 getTangentFrame( vec3 eye_pos, vec3 surf_norm, vec2 uv ) {
		vec3 q0 = dFdx( eye_pos.xyz );
		vec3 q1 = dFdy( eye_pos.xyz );
		vec2 st0 = dFdx( uv.st );
		vec2 st1 = dFdy( uv.st );
		vec3 N = surf_norm;
		vec3 q1perp = cross( q1, N );
		vec3 q0perp = cross( N, q0 );
		vec3 T = q1perp * st0.x + q0perp * st1.x;
		vec3 B = q1perp * st0.y + q0perp * st1.y;
		float det = max( dot( T, T ), dot( B, B ) );
		float scale = ( det == 0.0 ) ? 0.0 : inversesqrt( det );
		return mat3( T * scale, B * scale, N );
	}
#endif`,x0=`#ifdef USE_CLEARCOAT
	vec3 clearcoatNormal = nonPerturbedNormal;
#endif`,_0=`#ifdef USE_CLEARCOAT_NORMALMAP
	vec3 clearcoatMapN = texture2D( clearcoatNormalMap, vClearcoatNormalMapUv ).xyz * 2.0 - 1.0;
	clearcoatMapN.xy *= clearcoatNormalScale;
	clearcoatNormal = normalize( tbn2 * clearcoatMapN );
#endif`,v0=`#ifdef USE_CLEARCOATMAP
	uniform sampler2D clearcoatMap;
#endif
#ifdef USE_CLEARCOAT_NORMALMAP
	uniform sampler2D clearcoatNormalMap;
	uniform vec2 clearcoatNormalScale;
#endif
#ifdef USE_CLEARCOAT_ROUGHNESSMAP
	uniform sampler2D clearcoatRoughnessMap;
#endif`,y0=`#ifdef USE_IRIDESCENCEMAP
	uniform sampler2D iridescenceMap;
#endif
#ifdef USE_IRIDESCENCE_THICKNESSMAP
	uniform sampler2D iridescenceThicknessMap;
#endif`,M0=`#ifdef OPAQUE
diffuseColor.a = 1.0;
#endif
#ifdef USE_TRANSMISSION
diffuseColor.a *= material.transmissionAlpha;
#endif
gl_FragColor = vec4( outgoingLight, diffuseColor.a );`,S0=`vec3 packNormalToRGB( const in vec3 normal ) {
	return normalize( normal ) * 0.5 + 0.5;
}
vec3 unpackRGBToNormal( const in vec3 rgb ) {
	return 2.0 * rgb.xyz - 1.0;
}
const float PackUpscale = 256. / 255.;const float UnpackDownscale = 255. / 256.;const float ShiftRight8 = 1. / 256.;
const float Inv255 = 1. / 255.;
const vec4 PackFactors = vec4( 1.0, 256.0, 256.0 * 256.0, 256.0 * 256.0 * 256.0 );
const vec2 UnpackFactors2 = vec2( UnpackDownscale, 1.0 / PackFactors.g );
const vec3 UnpackFactors3 = vec3( UnpackDownscale / PackFactors.rg, 1.0 / PackFactors.b );
const vec4 UnpackFactors4 = vec4( UnpackDownscale / PackFactors.rgb, 1.0 / PackFactors.a );
vec4 packDepthToRGBA( const in float v ) {
	if( v <= 0.0 )
		return vec4( 0., 0., 0., 0. );
	if( v >= 1.0 )
		return vec4( 1., 1., 1., 1. );
	float vuf;
	float af = modf( v * PackFactors.a, vuf );
	float bf = modf( vuf * ShiftRight8, vuf );
	float gf = modf( vuf * ShiftRight8, vuf );
	return vec4( vuf * Inv255, gf * PackUpscale, bf * PackUpscale, af );
}
vec3 packDepthToRGB( const in float v ) {
	if( v <= 0.0 )
		return vec3( 0., 0., 0. );
	if( v >= 1.0 )
		return vec3( 1., 1., 1. );
	float vuf;
	float bf = modf( v * PackFactors.b, vuf );
	float gf = modf( vuf * ShiftRight8, vuf );
	return vec3( vuf * Inv255, gf * PackUpscale, bf );
}
vec2 packDepthToRG( const in float v ) {
	if( v <= 0.0 )
		return vec2( 0., 0. );
	if( v >= 1.0 )
		return vec2( 1., 1. );
	float vuf;
	float gf = modf( v * 256., vuf );
	return vec2( vuf * Inv255, gf );
}
float unpackRGBAToDepth( const in vec4 v ) {
	return dot( v, UnpackFactors4 );
}
float unpackRGBToDepth( const in vec3 v ) {
	return dot( v, UnpackFactors3 );
}
float unpackRGToDepth( const in vec2 v ) {
	return v.r * UnpackFactors2.r + v.g * UnpackFactors2.g;
}
vec4 pack2HalfToRGBA( const in vec2 v ) {
	vec4 r = vec4( v.x, fract( v.x * 255.0 ), v.y, fract( v.y * 255.0 ) );
	return vec4( r.x - r.y / 255.0, r.y, r.z - r.w / 255.0, r.w );
}
vec2 unpackRGBATo2Half( const in vec4 v ) {
	return vec2( v.x + ( v.y / 255.0 ), v.z + ( v.w / 255.0 ) );
}
float viewZToOrthographicDepth( const in float viewZ, const in float near, const in float far ) {
	return ( viewZ + near ) / ( near - far );
}
float orthographicDepthToViewZ( const in float depth, const in float near, const in float far ) {
	return depth * ( near - far ) - near;
}
float viewZToPerspectiveDepth( const in float viewZ, const in float near, const in float far ) {
	return ( ( near + viewZ ) * far ) / ( ( far - near ) * viewZ );
}
float perspectiveDepthToViewZ( const in float depth, const in float near, const in float far ) {
	return ( near * far ) / ( ( far - near ) * depth - far );
}`,b0=`#ifdef PREMULTIPLIED_ALPHA
	gl_FragColor.rgb *= gl_FragColor.a;
#endif`,E0=`vec4 mvPosition = vec4( transformed, 1.0 );
#ifdef USE_BATCHING
	mvPosition = batchingMatrix * mvPosition;
#endif
#ifdef USE_INSTANCING
	mvPosition = instanceMatrix * mvPosition;
#endif
mvPosition = modelViewMatrix * mvPosition;
gl_Position = projectionMatrix * mvPosition;`,w0=`#ifdef DITHERING
	gl_FragColor.rgb = dithering( gl_FragColor.rgb );
#endif`,T0=`#ifdef DITHERING
	vec3 dithering( vec3 color ) {
		float grid_position = rand( gl_FragCoord.xy );
		vec3 dither_shift_RGB = vec3( 0.25 / 255.0, -0.25 / 255.0, 0.25 / 255.0 );
		dither_shift_RGB = mix( 2.0 * dither_shift_RGB, -2.0 * dither_shift_RGB, grid_position );
		return color + dither_shift_RGB;
	}
#endif`,A0=`float roughnessFactor = roughness;
#ifdef USE_ROUGHNESSMAP
	vec4 texelRoughness = texture2D( roughnessMap, vRoughnessMapUv );
	roughnessFactor *= texelRoughness.g;
#endif`,C0=`#ifdef USE_ROUGHNESSMAP
	uniform sampler2D roughnessMap;
#endif`,R0=`#if NUM_SPOT_LIGHT_COORDS > 0
	varying vec4 vSpotLightCoord[ NUM_SPOT_LIGHT_COORDS ];
#endif
#if NUM_SPOT_LIGHT_MAPS > 0
	uniform sampler2D spotLightMap[ NUM_SPOT_LIGHT_MAPS ];
#endif
#ifdef USE_SHADOWMAP
	#if NUM_DIR_LIGHT_SHADOWS > 0
		uniform sampler2D directionalShadowMap[ NUM_DIR_LIGHT_SHADOWS ];
		varying vec4 vDirectionalShadowCoord[ NUM_DIR_LIGHT_SHADOWS ];
		struct DirectionalLightShadow {
			float shadowIntensity;
			float shadowBias;
			float shadowNormalBias;
			float shadowRadius;
			vec2 shadowMapSize;
		};
		uniform DirectionalLightShadow directionalLightShadows[ NUM_DIR_LIGHT_SHADOWS ];
	#endif
	#if NUM_SPOT_LIGHT_SHADOWS > 0
		uniform sampler2D spotShadowMap[ NUM_SPOT_LIGHT_SHADOWS ];
		struct SpotLightShadow {
			float shadowIntensity;
			float shadowBias;
			float shadowNormalBias;
			float shadowRadius;
			vec2 shadowMapSize;
		};
		uniform SpotLightShadow spotLightShadows[ NUM_SPOT_LIGHT_SHADOWS ];
	#endif
	#if NUM_POINT_LIGHT_SHADOWS > 0
		uniform sampler2D pointShadowMap[ NUM_POINT_LIGHT_SHADOWS ];
		varying vec4 vPointShadowCoord[ NUM_POINT_LIGHT_SHADOWS ];
		struct PointLightShadow {
			float shadowIntensity;
			float shadowBias;
			float shadowNormalBias;
			float shadowRadius;
			vec2 shadowMapSize;
			float shadowCameraNear;
			float shadowCameraFar;
		};
		uniform PointLightShadow pointLightShadows[ NUM_POINT_LIGHT_SHADOWS ];
	#endif
	float texture2DCompare( sampler2D depths, vec2 uv, float compare ) {
		return step( compare, unpackRGBAToDepth( texture2D( depths, uv ) ) );
	}
	vec2 texture2DDistribution( sampler2D shadow, vec2 uv ) {
		return unpackRGBATo2Half( texture2D( shadow, uv ) );
	}
	float VSMShadow (sampler2D shadow, vec2 uv, float compare ){
		float occlusion = 1.0;
		vec2 distribution = texture2DDistribution( shadow, uv );
		float hard_shadow = step( compare , distribution.x );
		if (hard_shadow != 1.0 ) {
			float distance = compare - distribution.x ;
			float variance = max( 0.00000, distribution.y * distribution.y );
			float softness_probability = variance / (variance + distance * distance );			softness_probability = clamp( ( softness_probability - 0.3 ) / ( 0.95 - 0.3 ), 0.0, 1.0 );			occlusion = clamp( max( hard_shadow, softness_probability ), 0.0, 1.0 );
		}
		return occlusion;
	}
	float getShadow( sampler2D shadowMap, vec2 shadowMapSize, float shadowIntensity, float shadowBias, float shadowRadius, vec4 shadowCoord ) {
		float shadow = 1.0;
		shadowCoord.xyz /= shadowCoord.w;
		shadowCoord.z += shadowBias;
		bool inFrustum = shadowCoord.x >= 0.0 && shadowCoord.x <= 1.0 && shadowCoord.y >= 0.0 && shadowCoord.y <= 1.0;
		bool frustumTest = inFrustum && shadowCoord.z <= 1.0;
		if ( frustumTest ) {
		#if defined( SHADOWMAP_TYPE_PCF )
			vec2 texelSize = vec2( 1.0 ) / shadowMapSize;
			float dx0 = - texelSize.x * shadowRadius;
			float dy0 = - texelSize.y * shadowRadius;
			float dx1 = + texelSize.x * shadowRadius;
			float dy1 = + texelSize.y * shadowRadius;
			float dx2 = dx0 / 2.0;
			float dy2 = dy0 / 2.0;
			float dx3 = dx1 / 2.0;
			float dy3 = dy1 / 2.0;
			shadow = (
				texture2DCompare( shadowMap, shadowCoord.xy + vec2( dx0, dy0 ), shadowCoord.z ) +
				texture2DCompare( shadowMap, shadowCoord.xy + vec2( 0.0, dy0 ), shadowCoord.z ) +
				texture2DCompare( shadowMap, shadowCoord.xy + vec2( dx1, dy0 ), shadowCoord.z ) +
				texture2DCompare( shadowMap, shadowCoord.xy + vec2( dx2, dy2 ), shadowCoord.z ) +
				texture2DCompare( shadowMap, shadowCoord.xy + vec2( 0.0, dy2 ), shadowCoord.z ) +
				texture2DCompare( shadowMap, shadowCoord.xy + vec2( dx3, dy2 ), shadowCoord.z ) +
				texture2DCompare( shadowMap, shadowCoord.xy + vec2( dx0, 0.0 ), shadowCoord.z ) +
				texture2DCompare( shadowMap, shadowCoord.xy + vec2( dx2, 0.0 ), shadowCoord.z ) +
				texture2DCompare( shadowMap, shadowCoord.xy, shadowCoord.z ) +
				texture2DCompare( shadowMap, shadowCoord.xy + vec2( dx3, 0.0 ), shadowCoord.z ) +
				texture2DCompare( shadowMap, shadowCoord.xy + vec2( dx1, 0.0 ), shadowCoord.z ) +
				texture2DCompare( shadowMap, shadowCoord.xy + vec2( dx2, dy3 ), shadowCoord.z ) +
				texture2DCompare( shadowMap, shadowCoord.xy + vec2( 0.0, dy3 ), shadowCoord.z ) +
				texture2DCompare( shadowMap, shadowCoord.xy + vec2( dx3, dy3 ), shadowCoord.z ) +
				texture2DCompare( shadowMap, shadowCoord.xy + vec2( dx0, dy1 ), shadowCoord.z ) +
				texture2DCompare( shadowMap, shadowCoord.xy + vec2( 0.0, dy1 ), shadowCoord.z ) +
				texture2DCompare( shadowMap, shadowCoord.xy + vec2( dx1, dy1 ), shadowCoord.z )
			) * ( 1.0 / 17.0 );
		#elif defined( SHADOWMAP_TYPE_PCF_SOFT )
			vec2 texelSize = vec2( 1.0 ) / shadowMapSize;
			float dx = texelSize.x;
			float dy = texelSize.y;
			vec2 uv = shadowCoord.xy;
			vec2 f = fract( uv * shadowMapSize + 0.5 );
			uv -= f * texelSize;
			shadow = (
				texture2DCompare( shadowMap, uv, shadowCoord.z ) +
				texture2DCompare( shadowMap, uv + vec2( dx, 0.0 ), shadowCoord.z ) +
				texture2DCompare( shadowMap, uv + vec2( 0.0, dy ), shadowCoord.z ) +
				texture2DCompare( shadowMap, uv + texelSize, shadowCoord.z ) +
				mix( texture2DCompare( shadowMap, uv + vec2( -dx, 0.0 ), shadowCoord.z ),
					 texture2DCompare( shadowMap, uv + vec2( 2.0 * dx, 0.0 ), shadowCoord.z ),
					 f.x ) +
				mix( texture2DCompare( shadowMap, uv + vec2( -dx, dy ), shadowCoord.z ),
					 texture2DCompare( shadowMap, uv + vec2( 2.0 * dx, dy ), shadowCoord.z ),
					 f.x ) +
				mix( texture2DCompare( shadowMap, uv + vec2( 0.0, -dy ), shadowCoord.z ),
					 texture2DCompare( shadowMap, uv + vec2( 0.0, 2.0 * dy ), shadowCoord.z ),
					 f.y ) +
				mix( texture2DCompare( shadowMap, uv + vec2( dx, -dy ), shadowCoord.z ),
					 texture2DCompare( shadowMap, uv + vec2( dx, 2.0 * dy ), shadowCoord.z ),
					 f.y ) +
				mix( mix( texture2DCompare( shadowMap, uv + vec2( -dx, -dy ), shadowCoord.z ),
						  texture2DCompare( shadowMap, uv + vec2( 2.0 * dx, -dy ), shadowCoord.z ),
						  f.x ),
					 mix( texture2DCompare( shadowMap, uv + vec2( -dx, 2.0 * dy ), shadowCoord.z ),
						  texture2DCompare( shadowMap, uv + vec2( 2.0 * dx, 2.0 * dy ), shadowCoord.z ),
						  f.x ),
					 f.y )
			) * ( 1.0 / 9.0 );
		#elif defined( SHADOWMAP_TYPE_VSM )
			shadow = VSMShadow( shadowMap, shadowCoord.xy, shadowCoord.z );
		#else
			shadow = texture2DCompare( shadowMap, shadowCoord.xy, shadowCoord.z );
		#endif
		}
		return mix( 1.0, shadow, shadowIntensity );
	}
	vec2 cubeToUV( vec3 v, float texelSizeY ) {
		vec3 absV = abs( v );
		float scaleToCube = 1.0 / max( absV.x, max( absV.y, absV.z ) );
		absV *= scaleToCube;
		v *= scaleToCube * ( 1.0 - 2.0 * texelSizeY );
		vec2 planar = v.xy;
		float almostATexel = 1.5 * texelSizeY;
		float almostOne = 1.0 - almostATexel;
		if ( absV.z >= almostOne ) {
			if ( v.z > 0.0 )
				planar.x = 4.0 - v.x;
		} else if ( absV.x >= almostOne ) {
			float signX = sign( v.x );
			planar.x = v.z * signX + 2.0 * signX;
		} else if ( absV.y >= almostOne ) {
			float signY = sign( v.y );
			planar.x = v.x + 2.0 * signY + 2.0;
			planar.y = v.z * signY - 2.0;
		}
		return vec2( 0.125, 0.25 ) * planar + vec2( 0.375, 0.75 );
	}
	float getPointShadow( sampler2D shadowMap, vec2 shadowMapSize, float shadowIntensity, float shadowBias, float shadowRadius, vec4 shadowCoord, float shadowCameraNear, float shadowCameraFar ) {
		float shadow = 1.0;
		vec3 lightToPosition = shadowCoord.xyz;
		
		float lightToPositionLength = length( lightToPosition );
		if ( lightToPositionLength - shadowCameraFar <= 0.0 && lightToPositionLength - shadowCameraNear >= 0.0 ) {
			float dp = ( lightToPositionLength - shadowCameraNear ) / ( shadowCameraFar - shadowCameraNear );			dp += shadowBias;
			vec3 bd3D = normalize( lightToPosition );
			vec2 texelSize = vec2( 1.0 ) / ( shadowMapSize * vec2( 4.0, 2.0 ) );
			#if defined( SHADOWMAP_TYPE_PCF ) || defined( SHADOWMAP_TYPE_PCF_SOFT ) || defined( SHADOWMAP_TYPE_VSM )
				vec2 offset = vec2( - 1, 1 ) * shadowRadius * texelSize.y;
				shadow = (
					texture2DCompare( shadowMap, cubeToUV( bd3D + offset.xyy, texelSize.y ), dp ) +
					texture2DCompare( shadowMap, cubeToUV( bd3D + offset.yyy, texelSize.y ), dp ) +
					texture2DCompare( shadowMap, cubeToUV( bd3D + offset.xyx, texelSize.y ), dp ) +
					texture2DCompare( shadowMap, cubeToUV( bd3D + offset.yyx, texelSize.y ), dp ) +
					texture2DCompare( shadowMap, cubeToUV( bd3D, texelSize.y ), dp ) +
					texture2DCompare( shadowMap, cubeToUV( bd3D + offset.xxy, texelSize.y ), dp ) +
					texture2DCompare( shadowMap, cubeToUV( bd3D + offset.yxy, texelSize.y ), dp ) +
					texture2DCompare( shadowMap, cubeToUV( bd3D + offset.xxx, texelSize.y ), dp ) +
					texture2DCompare( shadowMap, cubeToUV( bd3D + offset.yxx, texelSize.y ), dp )
				) * ( 1.0 / 9.0 );
			#else
				shadow = texture2DCompare( shadowMap, cubeToUV( bd3D, texelSize.y ), dp );
			#endif
		}
		return mix( 1.0, shadow, shadowIntensity );
	}
#endif`,P0=`#if NUM_SPOT_LIGHT_COORDS > 0
	uniform mat4 spotLightMatrix[ NUM_SPOT_LIGHT_COORDS ];
	varying vec4 vSpotLightCoord[ NUM_SPOT_LIGHT_COORDS ];
#endif
#ifdef USE_SHADOWMAP
	#if NUM_DIR_LIGHT_SHADOWS > 0
		uniform mat4 directionalShadowMatrix[ NUM_DIR_LIGHT_SHADOWS ];
		varying vec4 vDirectionalShadowCoord[ NUM_DIR_LIGHT_SHADOWS ];
		struct DirectionalLightShadow {
			float shadowIntensity;
			float shadowBias;
			float shadowNormalBias;
			float shadowRadius;
			vec2 shadowMapSize;
		};
		uniform DirectionalLightShadow directionalLightShadows[ NUM_DIR_LIGHT_SHADOWS ];
	#endif
	#if NUM_SPOT_LIGHT_SHADOWS > 0
		struct SpotLightShadow {
			float shadowIntensity;
			float shadowBias;
			float shadowNormalBias;
			float shadowRadius;
			vec2 shadowMapSize;
		};
		uniform SpotLightShadow spotLightShadows[ NUM_SPOT_LIGHT_SHADOWS ];
	#endif
	#if NUM_POINT_LIGHT_SHADOWS > 0
		uniform mat4 pointShadowMatrix[ NUM_POINT_LIGHT_SHADOWS ];
		varying vec4 vPointShadowCoord[ NUM_POINT_LIGHT_SHADOWS ];
		struct PointLightShadow {
			float shadowIntensity;
			float shadowBias;
			float shadowNormalBias;
			float shadowRadius;
			vec2 shadowMapSize;
			float shadowCameraNear;
			float shadowCameraFar;
		};
		uniform PointLightShadow pointLightShadows[ NUM_POINT_LIGHT_SHADOWS ];
	#endif
#endif`,I0=`#if ( defined( USE_SHADOWMAP ) && ( NUM_DIR_LIGHT_SHADOWS > 0 || NUM_POINT_LIGHT_SHADOWS > 0 ) ) || ( NUM_SPOT_LIGHT_COORDS > 0 )
	vec3 shadowWorldNormal = inverseTransformDirection( transformedNormal, viewMatrix );
	vec4 shadowWorldPosition;
#endif
#if defined( USE_SHADOWMAP )
	#if NUM_DIR_LIGHT_SHADOWS > 0
		#pragma unroll_loop_start
		for ( int i = 0; i < NUM_DIR_LIGHT_SHADOWS; i ++ ) {
			shadowWorldPosition = worldPosition + vec4( shadowWorldNormal * directionalLightShadows[ i ].shadowNormalBias, 0 );
			vDirectionalShadowCoord[ i ] = directionalShadowMatrix[ i ] * shadowWorldPosition;
		}
		#pragma unroll_loop_end
	#endif
	#if NUM_POINT_LIGHT_SHADOWS > 0
		#pragma unroll_loop_start
		for ( int i = 0; i < NUM_POINT_LIGHT_SHADOWS; i ++ ) {
			shadowWorldPosition = worldPosition + vec4( shadowWorldNormal * pointLightShadows[ i ].shadowNormalBias, 0 );
			vPointShadowCoord[ i ] = pointShadowMatrix[ i ] * shadowWorldPosition;
		}
		#pragma unroll_loop_end
	#endif
#endif
#if NUM_SPOT_LIGHT_COORDS > 0
	#pragma unroll_loop_start
	for ( int i = 0; i < NUM_SPOT_LIGHT_COORDS; i ++ ) {
		shadowWorldPosition = worldPosition;
		#if ( defined( USE_SHADOWMAP ) && UNROLLED_LOOP_INDEX < NUM_SPOT_LIGHT_SHADOWS )
			shadowWorldPosition.xyz += shadowWorldNormal * spotLightShadows[ i ].shadowNormalBias;
		#endif
		vSpotLightCoord[ i ] = spotLightMatrix[ i ] * shadowWorldPosition;
	}
	#pragma unroll_loop_end
#endif`,L0=`float getShadowMask() {
	float shadow = 1.0;
	#ifdef USE_SHADOWMAP
	#if NUM_DIR_LIGHT_SHADOWS > 0
	DirectionalLightShadow directionalLight;
	#pragma unroll_loop_start
	for ( int i = 0; i < NUM_DIR_LIGHT_SHADOWS; i ++ ) {
		directionalLight = directionalLightShadows[ i ];
		shadow *= receiveShadow ? getShadow( directionalShadowMap[ i ], directionalLight.shadowMapSize, directionalLight.shadowIntensity, directionalLight.shadowBias, directionalLight.shadowRadius, vDirectionalShadowCoord[ i ] ) : 1.0;
	}
	#pragma unroll_loop_end
	#endif
	#if NUM_SPOT_LIGHT_SHADOWS > 0
	SpotLightShadow spotLight;
	#pragma unroll_loop_start
	for ( int i = 0; i < NUM_SPOT_LIGHT_SHADOWS; i ++ ) {
		spotLight = spotLightShadows[ i ];
		shadow *= receiveShadow ? getShadow( spotShadowMap[ i ], spotLight.shadowMapSize, spotLight.shadowIntensity, spotLight.shadowBias, spotLight.shadowRadius, vSpotLightCoord[ i ] ) : 1.0;
	}
	#pragma unroll_loop_end
	#endif
	#if NUM_POINT_LIGHT_SHADOWS > 0
	PointLightShadow pointLight;
	#pragma unroll_loop_start
	for ( int i = 0; i < NUM_POINT_LIGHT_SHADOWS; i ++ ) {
		pointLight = pointLightShadows[ i ];
		shadow *= receiveShadow ? getPointShadow( pointShadowMap[ i ], pointLight.shadowMapSize, pointLight.shadowIntensity, pointLight.shadowBias, pointLight.shadowRadius, vPointShadowCoord[ i ], pointLight.shadowCameraNear, pointLight.shadowCameraFar ) : 1.0;
	}
	#pragma unroll_loop_end
	#endif
	#endif
	return shadow;
}`,D0=`#ifdef USE_SKINNING
	mat4 boneMatX = getBoneMatrix( skinIndex.x );
	mat4 boneMatY = getBoneMatrix( skinIndex.y );
	mat4 boneMatZ = getBoneMatrix( skinIndex.z );
	mat4 boneMatW = getBoneMatrix( skinIndex.w );
#endif`,U0=`#ifdef USE_SKINNING
	uniform mat4 bindMatrix;
	uniform mat4 bindMatrixInverse;
	uniform highp sampler2D boneTexture;
	mat4 getBoneMatrix( const in float i ) {
		int size = textureSize( boneTexture, 0 ).x;
		int j = int( i ) * 4;
		int x = j % size;
		int y = j / size;
		vec4 v1 = texelFetch( boneTexture, ivec2( x, y ), 0 );
		vec4 v2 = texelFetch( boneTexture, ivec2( x + 1, y ), 0 );
		vec4 v3 = texelFetch( boneTexture, ivec2( x + 2, y ), 0 );
		vec4 v4 = texelFetch( boneTexture, ivec2( x + 3, y ), 0 );
		return mat4( v1, v2, v3, v4 );
	}
#endif`,N0=`#ifdef USE_SKINNING
	vec4 skinVertex = bindMatrix * vec4( transformed, 1.0 );
	vec4 skinned = vec4( 0.0 );
	skinned += boneMatX * skinVertex * skinWeight.x;
	skinned += boneMatY * skinVertex * skinWeight.y;
	skinned += boneMatZ * skinVertex * skinWeight.z;
	skinned += boneMatW * skinVertex * skinWeight.w;
	transformed = ( bindMatrixInverse * skinned ).xyz;
#endif`,F0=`#ifdef USE_SKINNING
	mat4 skinMatrix = mat4( 0.0 );
	skinMatrix += skinWeight.x * boneMatX;
	skinMatrix += skinWeight.y * boneMatY;
	skinMatrix += skinWeight.z * boneMatZ;
	skinMatrix += skinWeight.w * boneMatW;
	skinMatrix = bindMatrixInverse * skinMatrix * bindMatrix;
	objectNormal = vec4( skinMatrix * vec4( objectNormal, 0.0 ) ).xyz;
	#ifdef USE_TANGENT
		objectTangent = vec4( skinMatrix * vec4( objectTangent, 0.0 ) ).xyz;
	#endif
#endif`,O0=`float specularStrength;
#ifdef USE_SPECULARMAP
	vec4 texelSpecular = texture2D( specularMap, vSpecularMapUv );
	specularStrength = texelSpecular.r;
#else
	specularStrength = 1.0;
#endif`,B0=`#ifdef USE_SPECULARMAP
	uniform sampler2D specularMap;
#endif`,V0=`#if defined( TONE_MAPPING )
	gl_FragColor.rgb = toneMapping( gl_FragColor.rgb );
#endif`,z0=`#ifndef saturate
#define saturate( a ) clamp( a, 0.0, 1.0 )
#endif
uniform float toneMappingExposure;
vec3 LinearToneMapping( vec3 color ) {
	return saturate( toneMappingExposure * color );
}
vec3 ReinhardToneMapping( vec3 color ) {
	color *= toneMappingExposure;
	return saturate( color / ( vec3( 1.0 ) + color ) );
}
vec3 CineonToneMapping( vec3 color ) {
	color *= toneMappingExposure;
	color = max( vec3( 0.0 ), color - 0.004 );
	return pow( ( color * ( 6.2 * color + 0.5 ) ) / ( color * ( 6.2 * color + 1.7 ) + 0.06 ), vec3( 2.2 ) );
}
vec3 RRTAndODTFit( vec3 v ) {
	vec3 a = v * ( v + 0.0245786 ) - 0.000090537;
	vec3 b = v * ( 0.983729 * v + 0.4329510 ) + 0.238081;
	return a / b;
}
vec3 ACESFilmicToneMapping( vec3 color ) {
	const mat3 ACESInputMat = mat3(
		vec3( 0.59719, 0.07600, 0.02840 ),		vec3( 0.35458, 0.90834, 0.13383 ),
		vec3( 0.04823, 0.01566, 0.83777 )
	);
	const mat3 ACESOutputMat = mat3(
		vec3(  1.60475, -0.10208, -0.00327 ),		vec3( -0.53108,  1.10813, -0.07276 ),
		vec3( -0.07367, -0.00605,  1.07602 )
	);
	color *= toneMappingExposure / 0.6;
	color = ACESInputMat * color;
	color = RRTAndODTFit( color );
	color = ACESOutputMat * color;
	return saturate( color );
}
const mat3 LINEAR_REC2020_TO_LINEAR_SRGB = mat3(
	vec3( 1.6605, - 0.1246, - 0.0182 ),
	vec3( - 0.5876, 1.1329, - 0.1006 ),
	vec3( - 0.0728, - 0.0083, 1.1187 )
);
const mat3 LINEAR_SRGB_TO_LINEAR_REC2020 = mat3(
	vec3( 0.6274, 0.0691, 0.0164 ),
	vec3( 0.3293, 0.9195, 0.0880 ),
	vec3( 0.0433, 0.0113, 0.8956 )
);
vec3 agxDefaultContrastApprox( vec3 x ) {
	vec3 x2 = x * x;
	vec3 x4 = x2 * x2;
	return + 15.5 * x4 * x2
		- 40.14 * x4 * x
		+ 31.96 * x4
		- 6.868 * x2 * x
		+ 0.4298 * x2
		+ 0.1191 * x
		- 0.00232;
}
vec3 AgXToneMapping( vec3 color ) {
	const mat3 AgXInsetMatrix = mat3(
		vec3( 0.856627153315983, 0.137318972929847, 0.11189821299995 ),
		vec3( 0.0951212405381588, 0.761241990602591, 0.0767994186031903 ),
		vec3( 0.0482516061458583, 0.101439036467562, 0.811302368396859 )
	);
	const mat3 AgXOutsetMatrix = mat3(
		vec3( 1.1271005818144368, - 0.1413297634984383, - 0.14132976349843826 ),
		vec3( - 0.11060664309660323, 1.157823702216272, - 0.11060664309660294 ),
		vec3( - 0.016493938717834573, - 0.016493938717834257, 1.2519364065950405 )
	);
	const float AgxMinEv = - 12.47393;	const float AgxMaxEv = 4.026069;
	color *= toneMappingExposure;
	color = LINEAR_SRGB_TO_LINEAR_REC2020 * color;
	color = AgXInsetMatrix * color;
	color = max( color, 1e-10 );	color = log2( color );
	color = ( color - AgxMinEv ) / ( AgxMaxEv - AgxMinEv );
	color = clamp( color, 0.0, 1.0 );
	color = agxDefaultContrastApprox( color );
	color = AgXOutsetMatrix * color;
	color = pow( max( vec3( 0.0 ), color ), vec3( 2.2 ) );
	color = LINEAR_REC2020_TO_LINEAR_SRGB * color;
	color = clamp( color, 0.0, 1.0 );
	return color;
}
vec3 NeutralToneMapping( vec3 color ) {
	const float StartCompression = 0.8 - 0.04;
	const float Desaturation = 0.15;
	color *= toneMappingExposure;
	float x = min( color.r, min( color.g, color.b ) );
	float offset = x < 0.08 ? x - 6.25 * x * x : 0.04;
	color -= offset;
	float peak = max( color.r, max( color.g, color.b ) );
	if ( peak < StartCompression ) return color;
	float d = 1. - StartCompression;
	float newPeak = 1. - d * d / ( peak + d - StartCompression );
	color *= newPeak / peak;
	float g = 1. - 1. / ( Desaturation * ( peak - newPeak ) + 1. );
	return mix( color, vec3( newPeak ), g );
}
vec3 CustomToneMapping( vec3 color ) { return color; }`,k0=`#ifdef USE_TRANSMISSION
	material.transmission = transmission;
	material.transmissionAlpha = 1.0;
	material.thickness = thickness;
	material.attenuationDistance = attenuationDistance;
	material.attenuationColor = attenuationColor;
	#ifdef USE_TRANSMISSIONMAP
		material.transmission *= texture2D( transmissionMap, vTransmissionMapUv ).r;
	#endif
	#ifdef USE_THICKNESSMAP
		material.thickness *= texture2D( thicknessMap, vThicknessMapUv ).g;
	#endif
	vec3 pos = vWorldPosition;
	vec3 v = normalize( cameraPosition - pos );
	vec3 n = inverseTransformDirection( normal, viewMatrix );
	vec4 transmitted = getIBLVolumeRefraction(
		n, v, material.roughness, material.diffuseColor, material.specularColor, material.specularF90,
		pos, modelMatrix, viewMatrix, projectionMatrix, material.dispersion, material.ior, material.thickness,
		material.attenuationColor, material.attenuationDistance );
	material.transmissionAlpha = mix( material.transmissionAlpha, transmitted.a, material.transmission );
	totalDiffuse = mix( totalDiffuse, transmitted.rgb, material.transmission );
#endif`,H0=`#ifdef USE_TRANSMISSION
	uniform float transmission;
	uniform float thickness;
	uniform float attenuationDistance;
	uniform vec3 attenuationColor;
	#ifdef USE_TRANSMISSIONMAP
		uniform sampler2D transmissionMap;
	#endif
	#ifdef USE_THICKNESSMAP
		uniform sampler2D thicknessMap;
	#endif
	uniform vec2 transmissionSamplerSize;
	uniform sampler2D transmissionSamplerMap;
	uniform mat4 modelMatrix;
	uniform mat4 projectionMatrix;
	varying vec3 vWorldPosition;
	float w0( float a ) {
		return ( 1.0 / 6.0 ) * ( a * ( a * ( - a + 3.0 ) - 3.0 ) + 1.0 );
	}
	float w1( float a ) {
		return ( 1.0 / 6.0 ) * ( a *  a * ( 3.0 * a - 6.0 ) + 4.0 );
	}
	float w2( float a ){
		return ( 1.0 / 6.0 ) * ( a * ( a * ( - 3.0 * a + 3.0 ) + 3.0 ) + 1.0 );
	}
	float w3( float a ) {
		return ( 1.0 / 6.0 ) * ( a * a * a );
	}
	float g0( float a ) {
		return w0( a ) + w1( a );
	}
	float g1( float a ) {
		return w2( a ) + w3( a );
	}
	float h0( float a ) {
		return - 1.0 + w1( a ) / ( w0( a ) + w1( a ) );
	}
	float h1( float a ) {
		return 1.0 + w3( a ) / ( w2( a ) + w3( a ) );
	}
	vec4 bicubic( sampler2D tex, vec2 uv, vec4 texelSize, float lod ) {
		uv = uv * texelSize.zw + 0.5;
		vec2 iuv = floor( uv );
		vec2 fuv = fract( uv );
		float g0x = g0( fuv.x );
		float g1x = g1( fuv.x );
		float h0x = h0( fuv.x );
		float h1x = h1( fuv.x );
		float h0y = h0( fuv.y );
		float h1y = h1( fuv.y );
		vec2 p0 = ( vec2( iuv.x + h0x, iuv.y + h0y ) - 0.5 ) * texelSize.xy;
		vec2 p1 = ( vec2( iuv.x + h1x, iuv.y + h0y ) - 0.5 ) * texelSize.xy;
		vec2 p2 = ( vec2( iuv.x + h0x, iuv.y + h1y ) - 0.5 ) * texelSize.xy;
		vec2 p3 = ( vec2( iuv.x + h1x, iuv.y + h1y ) - 0.5 ) * texelSize.xy;
		return g0( fuv.y ) * ( g0x * textureLod( tex, p0, lod ) + g1x * textureLod( tex, p1, lod ) ) +
			g1( fuv.y ) * ( g0x * textureLod( tex, p2, lod ) + g1x * textureLod( tex, p3, lod ) );
	}
	vec4 textureBicubic( sampler2D sampler, vec2 uv, float lod ) {
		vec2 fLodSize = vec2( textureSize( sampler, int( lod ) ) );
		vec2 cLodSize = vec2( textureSize( sampler, int( lod + 1.0 ) ) );
		vec2 fLodSizeInv = 1.0 / fLodSize;
		vec2 cLodSizeInv = 1.0 / cLodSize;
		vec4 fSample = bicubic( sampler, uv, vec4( fLodSizeInv, fLodSize ), floor( lod ) );
		vec4 cSample = bicubic( sampler, uv, vec4( cLodSizeInv, cLodSize ), ceil( lod ) );
		return mix( fSample, cSample, fract( lod ) );
	}
	vec3 getVolumeTransmissionRay( const in vec3 n, const in vec3 v, const in float thickness, const in float ior, const in mat4 modelMatrix ) {
		vec3 refractionVector = refract( - v, normalize( n ), 1.0 / ior );
		vec3 modelScale;
		modelScale.x = length( vec3( modelMatrix[ 0 ].xyz ) );
		modelScale.y = length( vec3( modelMatrix[ 1 ].xyz ) );
		modelScale.z = length( vec3( modelMatrix[ 2 ].xyz ) );
		return normalize( refractionVector ) * thickness * modelScale;
	}
	float applyIorToRoughness( const in float roughness, const in float ior ) {
		return roughness * clamp( ior * 2.0 - 2.0, 0.0, 1.0 );
	}
	vec4 getTransmissionSample( const in vec2 fragCoord, const in float roughness, const in float ior ) {
		float lod = log2( transmissionSamplerSize.x ) * applyIorToRoughness( roughness, ior );
		return textureBicubic( transmissionSamplerMap, fragCoord.xy, lod );
	}
	vec3 volumeAttenuation( const in float transmissionDistance, const in vec3 attenuationColor, const in float attenuationDistance ) {
		if ( isinf( attenuationDistance ) ) {
			return vec3( 1.0 );
		} else {
			vec3 attenuationCoefficient = -log( attenuationColor ) / attenuationDistance;
			vec3 transmittance = exp( - attenuationCoefficient * transmissionDistance );			return transmittance;
		}
	}
	vec4 getIBLVolumeRefraction( const in vec3 n, const in vec3 v, const in float roughness, const in vec3 diffuseColor,
		const in vec3 specularColor, const in float specularF90, const in vec3 position, const in mat4 modelMatrix,
		const in mat4 viewMatrix, const in mat4 projMatrix, const in float dispersion, const in float ior, const in float thickness,
		const in vec3 attenuationColor, const in float attenuationDistance ) {
		vec4 transmittedLight;
		vec3 transmittance;
		#ifdef USE_DISPERSION
			float halfSpread = ( ior - 1.0 ) * 0.025 * dispersion;
			vec3 iors = vec3( ior - halfSpread, ior, ior + halfSpread );
			for ( int i = 0; i < 3; i ++ ) {
				vec3 transmissionRay = getVolumeTransmissionRay( n, v, thickness, iors[ i ], modelMatrix );
				vec3 refractedRayExit = position + transmissionRay;
		
				vec4 ndcPos = projMatrix * viewMatrix * vec4( refractedRayExit, 1.0 );
				vec2 refractionCoords = ndcPos.xy / ndcPos.w;
				refractionCoords += 1.0;
				refractionCoords /= 2.0;
		
				vec4 transmissionSample = getTransmissionSample( refractionCoords, roughness, iors[ i ] );
				transmittedLight[ i ] = transmissionSample[ i ];
				transmittedLight.a += transmissionSample.a;
				transmittance[ i ] = diffuseColor[ i ] * volumeAttenuation( length( transmissionRay ), attenuationColor, attenuationDistance )[ i ];
			}
			transmittedLight.a /= 3.0;
		
		#else
		
			vec3 transmissionRay = getVolumeTransmissionRay( n, v, thickness, ior, modelMatrix );
			vec3 refractedRayExit = position + transmissionRay;
			vec4 ndcPos = projMatrix * viewMatrix * vec4( refractedRayExit, 1.0 );
			vec2 refractionCoords = ndcPos.xy / ndcPos.w;
			refractionCoords += 1.0;
			refractionCoords /= 2.0;
			transmittedLight = getTransmissionSample( refractionCoords, roughness, ior );
			transmittance = diffuseColor * volumeAttenuation( length( transmissionRay ), attenuationColor, attenuationDistance );
		
		#endif
		vec3 attenuatedColor = transmittance * transmittedLight.rgb;
		vec3 F = EnvironmentBRDF( n, v, specularColor, specularF90, roughness );
		float transmittanceFactor = ( transmittance.r + transmittance.g + transmittance.b ) / 3.0;
		return vec4( ( 1.0 - F ) * attenuatedColor, 1.0 - ( 1.0 - transmittedLight.a ) * transmittanceFactor );
	}
#endif`,G0=`#if defined( USE_UV ) || defined( USE_ANISOTROPY )
	varying vec2 vUv;
#endif
#ifdef USE_MAP
	varying vec2 vMapUv;
#endif
#ifdef USE_ALPHAMAP
	varying vec2 vAlphaMapUv;
#endif
#ifdef USE_LIGHTMAP
	varying vec2 vLightMapUv;
#endif
#ifdef USE_AOMAP
	varying vec2 vAoMapUv;
#endif
#ifdef USE_BUMPMAP
	varying vec2 vBumpMapUv;
#endif
#ifdef USE_NORMALMAP
	varying vec2 vNormalMapUv;
#endif
#ifdef USE_EMISSIVEMAP
	varying vec2 vEmissiveMapUv;
#endif
#ifdef USE_METALNESSMAP
	varying vec2 vMetalnessMapUv;
#endif
#ifdef USE_ROUGHNESSMAP
	varying vec2 vRoughnessMapUv;
#endif
#ifdef USE_ANISOTROPYMAP
	varying vec2 vAnisotropyMapUv;
#endif
#ifdef USE_CLEARCOATMAP
	varying vec2 vClearcoatMapUv;
#endif
#ifdef USE_CLEARCOAT_NORMALMAP
	varying vec2 vClearcoatNormalMapUv;
#endif
#ifdef USE_CLEARCOAT_ROUGHNESSMAP
	varying vec2 vClearcoatRoughnessMapUv;
#endif
#ifdef USE_IRIDESCENCEMAP
	varying vec2 vIridescenceMapUv;
#endif
#ifdef USE_IRIDESCENCE_THICKNESSMAP
	varying vec2 vIridescenceThicknessMapUv;
#endif
#ifdef USE_SHEEN_COLORMAP
	varying vec2 vSheenColorMapUv;
#endif
#ifdef USE_SHEEN_ROUGHNESSMAP
	varying vec2 vSheenRoughnessMapUv;
#endif
#ifdef USE_SPECULARMAP
	varying vec2 vSpecularMapUv;
#endif
#ifdef USE_SPECULAR_COLORMAP
	varying vec2 vSpecularColorMapUv;
#endif
#ifdef USE_SPECULAR_INTENSITYMAP
	varying vec2 vSpecularIntensityMapUv;
#endif
#ifdef USE_TRANSMISSIONMAP
	uniform mat3 transmissionMapTransform;
	varying vec2 vTransmissionMapUv;
#endif
#ifdef USE_THICKNESSMAP
	uniform mat3 thicknessMapTransform;
	varying vec2 vThicknessMapUv;
#endif`,W0=`#if defined( USE_UV ) || defined( USE_ANISOTROPY )
	varying vec2 vUv;
#endif
#ifdef USE_MAP
	uniform mat3 mapTransform;
	varying vec2 vMapUv;
#endif
#ifdef USE_ALPHAMAP
	uniform mat3 alphaMapTransform;
	varying vec2 vAlphaMapUv;
#endif
#ifdef USE_LIGHTMAP
	uniform mat3 lightMapTransform;
	varying vec2 vLightMapUv;
#endif
#ifdef USE_AOMAP
	uniform mat3 aoMapTransform;
	varying vec2 vAoMapUv;
#endif
#ifdef USE_BUMPMAP
	uniform mat3 bumpMapTransform;
	varying vec2 vBumpMapUv;
#endif
#ifdef USE_NORMALMAP
	uniform mat3 normalMapTransform;
	varying vec2 vNormalMapUv;
#endif
#ifdef USE_DISPLACEMENTMAP
	uniform mat3 displacementMapTransform;
	varying vec2 vDisplacementMapUv;
#endif
#ifdef USE_EMISSIVEMAP
	uniform mat3 emissiveMapTransform;
	varying vec2 vEmissiveMapUv;
#endif
#ifdef USE_METALNESSMAP
	uniform mat3 metalnessMapTransform;
	varying vec2 vMetalnessMapUv;
#endif
#ifdef USE_ROUGHNESSMAP
	uniform mat3 roughnessMapTransform;
	varying vec2 vRoughnessMapUv;
#endif
#ifdef USE_ANISOTROPYMAP
	uniform mat3 anisotropyMapTransform;
	varying vec2 vAnisotropyMapUv;
#endif
#ifdef USE_CLEARCOATMAP
	uniform mat3 clearcoatMapTransform;
	varying vec2 vClearcoatMapUv;
#endif
#ifdef USE_CLEARCOAT_NORMALMAP
	uniform mat3 clearcoatNormalMapTransform;
	varying vec2 vClearcoatNormalMapUv;
#endif
#ifdef USE_CLEARCOAT_ROUGHNESSMAP
	uniform mat3 clearcoatRoughnessMapTransform;
	varying vec2 vClearcoatRoughnessMapUv;
#endif
#ifdef USE_SHEEN_COLORMAP
	uniform mat3 sheenColorMapTransform;
	varying vec2 vSheenColorMapUv;
#endif
#ifdef USE_SHEEN_ROUGHNESSMAP
	uniform mat3 sheenRoughnessMapTransform;
	varying vec2 vSheenRoughnessMapUv;
#endif
#ifdef USE_IRIDESCENCEMAP
	uniform mat3 iridescenceMapTransform;
	varying vec2 vIridescenceMapUv;
#endif
#ifdef USE_IRIDESCENCE_THICKNESSMAP
	uniform mat3 iridescenceThicknessMapTransform;
	varying vec2 vIridescenceThicknessMapUv;
#endif
#ifdef USE_SPECULARMAP
	uniform mat3 specularMapTransform;
	varying vec2 vSpecularMapUv;
#endif
#ifdef USE_SPECULAR_COLORMAP
	uniform mat3 specularColorMapTransform;
	varying vec2 vSpecularColorMapUv;
#endif
#ifdef USE_SPECULAR_INTENSITYMAP
	uniform mat3 specularIntensityMapTransform;
	varying vec2 vSpecularIntensityMapUv;
#endif
#ifdef USE_TRANSMISSIONMAP
	uniform mat3 transmissionMapTransform;
	varying vec2 vTransmissionMapUv;
#endif
#ifdef USE_THICKNESSMAP
	uniform mat3 thicknessMapTransform;
	varying vec2 vThicknessMapUv;
#endif`,X0=`#if defined( USE_UV ) || defined( USE_ANISOTROPY )
	vUv = vec3( uv, 1 ).xy;
#endif
#ifdef USE_MAP
	vMapUv = ( mapTransform * vec3( MAP_UV, 1 ) ).xy;
#endif
#ifdef USE_ALPHAMAP
	vAlphaMapUv = ( alphaMapTransform * vec3( ALPHAMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_LIGHTMAP
	vLightMapUv = ( lightMapTransform * vec3( LIGHTMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_AOMAP
	vAoMapUv = ( aoMapTransform * vec3( AOMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_BUMPMAP
	vBumpMapUv = ( bumpMapTransform * vec3( BUMPMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_NORMALMAP
	vNormalMapUv = ( normalMapTransform * vec3( NORMALMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_DISPLACEMENTMAP
	vDisplacementMapUv = ( displacementMapTransform * vec3( DISPLACEMENTMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_EMISSIVEMAP
	vEmissiveMapUv = ( emissiveMapTransform * vec3( EMISSIVEMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_METALNESSMAP
	vMetalnessMapUv = ( metalnessMapTransform * vec3( METALNESSMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_ROUGHNESSMAP
	vRoughnessMapUv = ( roughnessMapTransform * vec3( ROUGHNESSMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_ANISOTROPYMAP
	vAnisotropyMapUv = ( anisotropyMapTransform * vec3( ANISOTROPYMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_CLEARCOATMAP
	vClearcoatMapUv = ( clearcoatMapTransform * vec3( CLEARCOATMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_CLEARCOAT_NORMALMAP
	vClearcoatNormalMapUv = ( clearcoatNormalMapTransform * vec3( CLEARCOAT_NORMALMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_CLEARCOAT_ROUGHNESSMAP
	vClearcoatRoughnessMapUv = ( clearcoatRoughnessMapTransform * vec3( CLEARCOAT_ROUGHNESSMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_IRIDESCENCEMAP
	vIridescenceMapUv = ( iridescenceMapTransform * vec3( IRIDESCENCEMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_IRIDESCENCE_THICKNESSMAP
	vIridescenceThicknessMapUv = ( iridescenceThicknessMapTransform * vec3( IRIDESCENCE_THICKNESSMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_SHEEN_COLORMAP
	vSheenColorMapUv = ( sheenColorMapTransform * vec3( SHEEN_COLORMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_SHEEN_ROUGHNESSMAP
	vSheenRoughnessMapUv = ( sheenRoughnessMapTransform * vec3( SHEEN_ROUGHNESSMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_SPECULARMAP
	vSpecularMapUv = ( specularMapTransform * vec3( SPECULARMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_SPECULAR_COLORMAP
	vSpecularColorMapUv = ( specularColorMapTransform * vec3( SPECULAR_COLORMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_SPECULAR_INTENSITYMAP
	vSpecularIntensityMapUv = ( specularIntensityMapTransform * vec3( SPECULAR_INTENSITYMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_TRANSMISSIONMAP
	vTransmissionMapUv = ( transmissionMapTransform * vec3( TRANSMISSIONMAP_UV, 1 ) ).xy;
#endif
#ifdef USE_THICKNESSMAP
	vThicknessMapUv = ( thicknessMapTransform * vec3( THICKNESSMAP_UV, 1 ) ).xy;
#endif`,q0=`#if defined( USE_ENVMAP ) || defined( DISTANCE ) || defined ( USE_SHADOWMAP ) || defined ( USE_TRANSMISSION ) || NUM_SPOT_LIGHT_COORDS > 0
	vec4 worldPosition = vec4( transformed, 1.0 );
	#ifdef USE_BATCHING
		worldPosition = batchingMatrix * worldPosition;
	#endif
	#ifdef USE_INSTANCING
		worldPosition = instanceMatrix * worldPosition;
	#endif
	worldPosition = modelMatrix * worldPosition;
#endif`,Y0=`varying vec2 vUv;
uniform mat3 uvTransform;
void main() {
	vUv = ( uvTransform * vec3( uv, 1 ) ).xy;
	gl_Position = vec4( position.xy, 1.0, 1.0 );
}`,K0=`uniform sampler2D t2D;
uniform float backgroundIntensity;
varying vec2 vUv;
void main() {
	vec4 texColor = texture2D( t2D, vUv );
	#ifdef DECODE_VIDEO_TEXTURE
		texColor = vec4( mix( pow( texColor.rgb * 0.9478672986 + vec3( 0.0521327014 ), vec3( 2.4 ) ), texColor.rgb * 0.0773993808, vec3( lessThanEqual( texColor.rgb, vec3( 0.04045 ) ) ) ), texColor.w );
	#endif
	texColor.rgb *= backgroundIntensity;
	gl_FragColor = texColor;
	#include <tonemapping_fragment>
	#include <colorspace_fragment>
}`,$0=`varying vec3 vWorldDirection;
#include <common>
void main() {
	vWorldDirection = transformDirection( position, modelMatrix );
	#include <begin_vertex>
	#include <project_vertex>
	gl_Position.z = gl_Position.w;
}`,Z0=`#ifdef ENVMAP_TYPE_CUBE
	uniform samplerCube envMap;
#elif defined( ENVMAP_TYPE_CUBE_UV )
	uniform sampler2D envMap;
#endif
uniform float flipEnvMap;
uniform float backgroundBlurriness;
uniform float backgroundIntensity;
uniform mat3 backgroundRotation;
varying vec3 vWorldDirection;
#include <cube_uv_reflection_fragment>
void main() {
	#ifdef ENVMAP_TYPE_CUBE
		vec4 texColor = textureCube( envMap, backgroundRotation * vec3( flipEnvMap * vWorldDirection.x, vWorldDirection.yz ) );
	#elif defined( ENVMAP_TYPE_CUBE_UV )
		vec4 texColor = textureCubeUV( envMap, backgroundRotation * vWorldDirection, backgroundBlurriness );
	#else
		vec4 texColor = vec4( 0.0, 0.0, 0.0, 1.0 );
	#endif
	texColor.rgb *= backgroundIntensity;
	gl_FragColor = texColor;
	#include <tonemapping_fragment>
	#include <colorspace_fragment>
}`,J0=`varying vec3 vWorldDirection;
#include <common>
void main() {
	vWorldDirection = transformDirection( position, modelMatrix );
	#include <begin_vertex>
	#include <project_vertex>
	gl_Position.z = gl_Position.w;
}`,Q0=`uniform samplerCube tCube;
uniform float tFlip;
uniform float opacity;
varying vec3 vWorldDirection;
void main() {
	vec4 texColor = textureCube( tCube, vec3( tFlip * vWorldDirection.x, vWorldDirection.yz ) );
	gl_FragColor = texColor;
	gl_FragColor.a *= opacity;
	#include <tonemapping_fragment>
	#include <colorspace_fragment>
}`,j0=`#include <common>
#include <batching_pars_vertex>
#include <uv_pars_vertex>
#include <displacementmap_pars_vertex>
#include <morphtarget_pars_vertex>
#include <skinning_pars_vertex>
#include <logdepthbuf_pars_vertex>
#include <clipping_planes_pars_vertex>
varying vec2 vHighPrecisionZW;
void main() {
	#include <uv_vertex>
	#include <batching_vertex>
	#include <skinbase_vertex>
	#include <morphinstance_vertex>
	#ifdef USE_DISPLACEMENTMAP
		#include <beginnormal_vertex>
		#include <morphnormal_vertex>
		#include <skinnormal_vertex>
	#endif
	#include <begin_vertex>
	#include <morphtarget_vertex>
	#include <skinning_vertex>
	#include <displacementmap_vertex>
	#include <project_vertex>
	#include <logdepthbuf_vertex>
	#include <clipping_planes_vertex>
	vHighPrecisionZW = gl_Position.zw;
}`,tx=`#if DEPTH_PACKING == 3200
	uniform float opacity;
#endif
#include <common>
#include <packing>
#include <uv_pars_fragment>
#include <map_pars_fragment>
#include <alphamap_pars_fragment>
#include <alphatest_pars_fragment>
#include <alphahash_pars_fragment>
#include <logdepthbuf_pars_fragment>
#include <clipping_planes_pars_fragment>
varying vec2 vHighPrecisionZW;
void main() {
	vec4 diffuseColor = vec4( 1.0 );
	#include <clipping_planes_fragment>
	#if DEPTH_PACKING == 3200
		diffuseColor.a = opacity;
	#endif
	#include <map_fragment>
	#include <alphamap_fragment>
	#include <alphatest_fragment>
	#include <alphahash_fragment>
	#include <logdepthbuf_fragment>
	float fragCoordZ = 0.5 * vHighPrecisionZW[0] / vHighPrecisionZW[1] + 0.5;
	#if DEPTH_PACKING == 3200
		gl_FragColor = vec4( vec3( 1.0 - fragCoordZ ), opacity );
	#elif DEPTH_PACKING == 3201
		gl_FragColor = packDepthToRGBA( fragCoordZ );
	#elif DEPTH_PACKING == 3202
		gl_FragColor = vec4( packDepthToRGB( fragCoordZ ), 1.0 );
	#elif DEPTH_PACKING == 3203
		gl_FragColor = vec4( packDepthToRG( fragCoordZ ), 0.0, 1.0 );
	#endif
}`,ex=`#define DISTANCE
varying vec3 vWorldPosition;
#include <common>
#include <batching_pars_vertex>
#include <uv_pars_vertex>
#include <displacementmap_pars_vertex>
#include <morphtarget_pars_vertex>
#include <skinning_pars_vertex>
#include <clipping_planes_pars_vertex>
void main() {
	#include <uv_vertex>
	#include <batching_vertex>
	#include <skinbase_vertex>
	#include <morphinstance_vertex>
	#ifdef USE_DISPLACEMENTMAP
		#include <beginnormal_vertex>
		#include <morphnormal_vertex>
		#include <skinnormal_vertex>
	#endif
	#include <begin_vertex>
	#include <morphtarget_vertex>
	#include <skinning_vertex>
	#include <displacementmap_vertex>
	#include <project_vertex>
	#include <worldpos_vertex>
	#include <clipping_planes_vertex>
	vWorldPosition = worldPosition.xyz;
}`,nx=`#define DISTANCE
uniform vec3 referencePosition;
uniform float nearDistance;
uniform float farDistance;
varying vec3 vWorldPosition;
#include <common>
#include <packing>
#include <uv_pars_fragment>
#include <map_pars_fragment>
#include <alphamap_pars_fragment>
#include <alphatest_pars_fragment>
#include <alphahash_pars_fragment>
#include <clipping_planes_pars_fragment>
void main () {
	vec4 diffuseColor = vec4( 1.0 );
	#include <clipping_planes_fragment>
	#include <map_fragment>
	#include <alphamap_fragment>
	#include <alphatest_fragment>
	#include <alphahash_fragment>
	float dist = length( vWorldPosition - referencePosition );
	dist = ( dist - nearDistance ) / ( farDistance - nearDistance );
	dist = saturate( dist );
	gl_FragColor = packDepthToRGBA( dist );
}`,ix=`varying vec3 vWorldDirection;
#include <common>
void main() {
	vWorldDirection = transformDirection( position, modelMatrix );
	#include <begin_vertex>
	#include <project_vertex>
}`,sx=`uniform sampler2D tEquirect;
varying vec3 vWorldDirection;
#include <common>
void main() {
	vec3 direction = normalize( vWorldDirection );
	vec2 sampleUV = equirectUv( direction );
	gl_FragColor = texture2D( tEquirect, sampleUV );
	#include <tonemapping_fragment>
	#include <colorspace_fragment>
}`,rx=`uniform float scale;
attribute float lineDistance;
varying float vLineDistance;
#include <common>
#include <uv_pars_vertex>
#include <color_pars_vertex>
#include <fog_pars_vertex>
#include <morphtarget_pars_vertex>
#include <logdepthbuf_pars_vertex>
#include <clipping_planes_pars_vertex>
void main() {
	vLineDistance = scale * lineDistance;
	#include <uv_vertex>
	#include <color_vertex>
	#include <morphinstance_vertex>
	#include <morphcolor_vertex>
	#include <begin_vertex>
	#include <morphtarget_vertex>
	#include <project_vertex>
	#include <logdepthbuf_vertex>
	#include <clipping_planes_vertex>
	#include <fog_vertex>
}`,ox=`uniform vec3 diffuse;
uniform float opacity;
uniform float dashSize;
uniform float totalSize;
varying float vLineDistance;
#include <common>
#include <color_pars_fragment>
#include <uv_pars_fragment>
#include <map_pars_fragment>
#include <fog_pars_fragment>
#include <logdepthbuf_pars_fragment>
#include <clipping_planes_pars_fragment>
void main() {
	vec4 diffuseColor = vec4( diffuse, opacity );
	#include <clipping_planes_fragment>
	if ( mod( vLineDistance, totalSize ) > dashSize ) {
		discard;
	}
	vec3 outgoingLight = vec3( 0.0 );
	#include <logdepthbuf_fragment>
	#include <map_fragment>
	#include <color_fragment>
	outgoingLight = diffuseColor.rgb;
	#include <opaque_fragment>
	#include <tonemapping_fragment>
	#include <colorspace_fragment>
	#include <fog_fragment>
	#include <premultiplied_alpha_fragment>
}`,ax=`#include <common>
#include <batching_pars_vertex>
#include <uv_pars_vertex>
#include <envmap_pars_vertex>
#include <color_pars_vertex>
#include <fog_pars_vertex>
#include <morphtarget_pars_vertex>
#include <skinning_pars_vertex>
#include <logdepthbuf_pars_vertex>
#include <clipping_planes_pars_vertex>
void main() {
	#include <uv_vertex>
	#include <color_vertex>
	#include <morphinstance_vertex>
	#include <morphcolor_vertex>
	#include <batching_vertex>
	#if defined ( USE_ENVMAP ) || defined ( USE_SKINNING )
		#include <beginnormal_vertex>
		#include <morphnormal_vertex>
		#include <skinbase_vertex>
		#include <skinnormal_vertex>
		#include <defaultnormal_vertex>
	#endif
	#include <begin_vertex>
	#include <morphtarget_vertex>
	#include <skinning_vertex>
	#include <project_vertex>
	#include <logdepthbuf_vertex>
	#include <clipping_planes_vertex>
	#include <worldpos_vertex>
	#include <envmap_vertex>
	#include <fog_vertex>
}`,lx=`uniform vec3 diffuse;
uniform float opacity;
#ifndef FLAT_SHADED
	varying vec3 vNormal;
#endif
#include <common>
#include <dithering_pars_fragment>
#include <color_pars_fragment>
#include <uv_pars_fragment>
#include <map_pars_fragment>
#include <alphamap_pars_fragment>
#include <alphatest_pars_fragment>
#include <alphahash_pars_fragment>
#include <aomap_pars_fragment>
#include <lightmap_pars_fragment>
#include <envmap_common_pars_fragment>
#include <envmap_pars_fragment>
#include <fog_pars_fragment>
#include <specularmap_pars_fragment>
#include <logdepthbuf_pars_fragment>
#include <clipping_planes_pars_fragment>
void main() {
	vec4 diffuseColor = vec4( diffuse, opacity );
	#include <clipping_planes_fragment>
	#include <logdepthbuf_fragment>
	#include <map_fragment>
	#include <color_fragment>
	#include <alphamap_fragment>
	#include <alphatest_fragment>
	#include <alphahash_fragment>
	#include <specularmap_fragment>
	ReflectedLight reflectedLight = ReflectedLight( vec3( 0.0 ), vec3( 0.0 ), vec3( 0.0 ), vec3( 0.0 ) );
	#ifdef USE_LIGHTMAP
		vec4 lightMapTexel = texture2D( lightMap, vLightMapUv );
		reflectedLight.indirectDiffuse += lightMapTexel.rgb * lightMapIntensity * RECIPROCAL_PI;
	#else
		reflectedLight.indirectDiffuse += vec3( 1.0 );
	#endif
	#include <aomap_fragment>
	reflectedLight.indirectDiffuse *= diffuseColor.rgb;
	vec3 outgoingLight = reflectedLight.indirectDiffuse;
	#include <envmap_fragment>
	#include <opaque_fragment>
	#include <tonemapping_fragment>
	#include <colorspace_fragment>
	#include <fog_fragment>
	#include <premultiplied_alpha_fragment>
	#include <dithering_fragment>
}`,cx=`#define LAMBERT
varying vec3 vViewPosition;
#include <common>
#include <batching_pars_vertex>
#include <uv_pars_vertex>
#include <displacementmap_pars_vertex>
#include <envmap_pars_vertex>
#include <color_pars_vertex>
#include <fog_pars_vertex>
#include <normal_pars_vertex>
#include <morphtarget_pars_vertex>
#include <skinning_pars_vertex>
#include <shadowmap_pars_vertex>
#include <logdepthbuf_pars_vertex>
#include <clipping_planes_pars_vertex>
void main() {
	#include <uv_vertex>
	#include <color_vertex>
	#include <morphinstance_vertex>
	#include <morphcolor_vertex>
	#include <batching_vertex>
	#include <beginnormal_vertex>
	#include <morphnormal_vertex>
	#include <skinbase_vertex>
	#include <skinnormal_vertex>
	#include <defaultnormal_vertex>
	#include <normal_vertex>
	#include <begin_vertex>
	#include <morphtarget_vertex>
	#include <skinning_vertex>
	#include <displacementmap_vertex>
	#include <project_vertex>
	#include <logdepthbuf_vertex>
	#include <clipping_planes_vertex>
	vViewPosition = - mvPosition.xyz;
	#include <worldpos_vertex>
	#include <envmap_vertex>
	#include <shadowmap_vertex>
	#include <fog_vertex>
}`,hx=`#define LAMBERT
uniform vec3 diffuse;
uniform vec3 emissive;
uniform float opacity;
#include <common>
#include <packing>
#include <dithering_pars_fragment>
#include <color_pars_fragment>
#include <uv_pars_fragment>
#include <map_pars_fragment>
#include <alphamap_pars_fragment>
#include <alphatest_pars_fragment>
#include <alphahash_pars_fragment>
#include <aomap_pars_fragment>
#include <lightmap_pars_fragment>
#include <emissivemap_pars_fragment>
#include <envmap_common_pars_fragment>
#include <envmap_pars_fragment>
#include <fog_pars_fragment>
#include <bsdfs>
#include <lights_pars_begin>
#include <normal_pars_fragment>
#include <lights_lambert_pars_fragment>
#include <shadowmap_pars_fragment>
#include <bumpmap_pars_fragment>
#include <normalmap_pars_fragment>
#include <specularmap_pars_fragment>
#include <logdepthbuf_pars_fragment>
#include <clipping_planes_pars_fragment>
void main() {
	vec4 diffuseColor = vec4( diffuse, opacity );
	#include <clipping_planes_fragment>
	ReflectedLight reflectedLight = ReflectedLight( vec3( 0.0 ), vec3( 0.0 ), vec3( 0.0 ), vec3( 0.0 ) );
	vec3 totalEmissiveRadiance = emissive;
	#include <logdepthbuf_fragment>
	#include <map_fragment>
	#include <color_fragment>
	#include <alphamap_fragment>
	#include <alphatest_fragment>
	#include <alphahash_fragment>
	#include <specularmap_fragment>
	#include <normal_fragment_begin>
	#include <normal_fragment_maps>
	#include <emissivemap_fragment>
	#include <lights_lambert_fragment>
	#include <lights_fragment_begin>
	#include <lights_fragment_maps>
	#include <lights_fragment_end>
	#include <aomap_fragment>
	vec3 outgoingLight = reflectedLight.directDiffuse + reflectedLight.indirectDiffuse + totalEmissiveRadiance;
	#include <envmap_fragment>
	#include <opaque_fragment>
	#include <tonemapping_fragment>
	#include <colorspace_fragment>
	#include <fog_fragment>
	#include <premultiplied_alpha_fragment>
	#include <dithering_fragment>
}`,ux=`#define MATCAP
varying vec3 vViewPosition;
#include <common>
#include <batching_pars_vertex>
#include <uv_pars_vertex>
#include <color_pars_vertex>
#include <displacementmap_pars_vertex>
#include <fog_pars_vertex>
#include <normal_pars_vertex>
#include <morphtarget_pars_vertex>
#include <skinning_pars_vertex>
#include <logdepthbuf_pars_vertex>
#include <clipping_planes_pars_vertex>
void main() {
	#include <uv_vertex>
	#include <color_vertex>
	#include <morphinstance_vertex>
	#include <morphcolor_vertex>
	#include <batching_vertex>
	#include <beginnormal_vertex>
	#include <morphnormal_vertex>
	#include <skinbase_vertex>
	#include <skinnormal_vertex>
	#include <defaultnormal_vertex>
	#include <normal_vertex>
	#include <begin_vertex>
	#include <morphtarget_vertex>
	#include <skinning_vertex>
	#include <displacementmap_vertex>
	#include <project_vertex>
	#include <logdepthbuf_vertex>
	#include <clipping_planes_vertex>
	#include <fog_vertex>
	vViewPosition = - mvPosition.xyz;
}`,fx=`#define MATCAP
uniform vec3 diffuse;
uniform float opacity;
uniform sampler2D matcap;
varying vec3 vViewPosition;
#include <common>
#include <dithering_pars_fragment>
#include <color_pars_fragment>
#include <uv_pars_fragment>
#include <map_pars_fragment>
#include <alphamap_pars_fragment>
#include <alphatest_pars_fragment>
#include <alphahash_pars_fragment>
#include <fog_pars_fragment>
#include <normal_pars_fragment>
#include <bumpmap_pars_fragment>
#include <normalmap_pars_fragment>
#include <logdepthbuf_pars_fragment>
#include <clipping_planes_pars_fragment>
void main() {
	vec4 diffuseColor = vec4( diffuse, opacity );
	#include <clipping_planes_fragment>
	#include <logdepthbuf_fragment>
	#include <map_fragment>
	#include <color_fragment>
	#include <alphamap_fragment>
	#include <alphatest_fragment>
	#include <alphahash_fragment>
	#include <normal_fragment_begin>
	#include <normal_fragment_maps>
	vec3 viewDir = normalize( vViewPosition );
	vec3 x = normalize( vec3( viewDir.z, 0.0, - viewDir.x ) );
	vec3 y = cross( viewDir, x );
	vec2 uv = vec2( dot( x, normal ), dot( y, normal ) ) * 0.495 + 0.5;
	#ifdef USE_MATCAP
		vec4 matcapColor = texture2D( matcap, uv );
	#else
		vec4 matcapColor = vec4( vec3( mix( 0.2, 0.8, uv.y ) ), 1.0 );
	#endif
	vec3 outgoingLight = diffuseColor.rgb * matcapColor.rgb;
	#include <opaque_fragment>
	#include <tonemapping_fragment>
	#include <colorspace_fragment>
	#include <fog_fragment>
	#include <premultiplied_alpha_fragment>
	#include <dithering_fragment>
}`,dx=`#define NORMAL
#if defined( FLAT_SHADED ) || defined( USE_BUMPMAP ) || defined( USE_NORMALMAP_TANGENTSPACE )
	varying vec3 vViewPosition;
#endif
#include <common>
#include <batching_pars_vertex>
#include <uv_pars_vertex>
#include <displacementmap_pars_vertex>
#include <normal_pars_vertex>
#include <morphtarget_pars_vertex>
#include <skinning_pars_vertex>
#include <logdepthbuf_pars_vertex>
#include <clipping_planes_pars_vertex>
void main() {
	#include <uv_vertex>
	#include <batching_vertex>
	#include <beginnormal_vertex>
	#include <morphinstance_vertex>
	#include <morphnormal_vertex>
	#include <skinbase_vertex>
	#include <skinnormal_vertex>
	#include <defaultnormal_vertex>
	#include <normal_vertex>
	#include <begin_vertex>
	#include <morphtarget_vertex>
	#include <skinning_vertex>
	#include <displacementmap_vertex>
	#include <project_vertex>
	#include <logdepthbuf_vertex>
	#include <clipping_planes_vertex>
#if defined( FLAT_SHADED ) || defined( USE_BUMPMAP ) || defined( USE_NORMALMAP_TANGENTSPACE )
	vViewPosition = - mvPosition.xyz;
#endif
}`,px=`#define NORMAL
uniform float opacity;
#if defined( FLAT_SHADED ) || defined( USE_BUMPMAP ) || defined( USE_NORMALMAP_TANGENTSPACE )
	varying vec3 vViewPosition;
#endif
#include <packing>
#include <uv_pars_fragment>
#include <normal_pars_fragment>
#include <bumpmap_pars_fragment>
#include <normalmap_pars_fragment>
#include <logdepthbuf_pars_fragment>
#include <clipping_planes_pars_fragment>
void main() {
	vec4 diffuseColor = vec4( 0.0, 0.0, 0.0, opacity );
	#include <clipping_planes_fragment>
	#include <logdepthbuf_fragment>
	#include <normal_fragment_begin>
	#include <normal_fragment_maps>
	gl_FragColor = vec4( packNormalToRGB( normal ), diffuseColor.a );
	#ifdef OPAQUE
		gl_FragColor.a = 1.0;
	#endif
}`,mx=`#define PHONG
varying vec3 vViewPosition;
#include <common>
#include <batching_pars_vertex>
#include <uv_pars_vertex>
#include <displacementmap_pars_vertex>
#include <envmap_pars_vertex>
#include <color_pars_vertex>
#include <fog_pars_vertex>
#include <normal_pars_vertex>
#include <morphtarget_pars_vertex>
#include <skinning_pars_vertex>
#include <shadowmap_pars_vertex>
#include <logdepthbuf_pars_vertex>
#include <clipping_planes_pars_vertex>
void main() {
	#include <uv_vertex>
	#include <color_vertex>
	#include <morphcolor_vertex>
	#include <batching_vertex>
	#include <beginnormal_vertex>
	#include <morphinstance_vertex>
	#include <morphnormal_vertex>
	#include <skinbase_vertex>
	#include <skinnormal_vertex>
	#include <defaultnormal_vertex>
	#include <normal_vertex>
	#include <begin_vertex>
	#include <morphtarget_vertex>
	#include <skinning_vertex>
	#include <displacementmap_vertex>
	#include <project_vertex>
	#include <logdepthbuf_vertex>
	#include <clipping_planes_vertex>
	vViewPosition = - mvPosition.xyz;
	#include <worldpos_vertex>
	#include <envmap_vertex>
	#include <shadowmap_vertex>
	#include <fog_vertex>
}`,gx=`#define PHONG
uniform vec3 diffuse;
uniform vec3 emissive;
uniform vec3 specular;
uniform float shininess;
uniform float opacity;
#include <common>
#include <packing>
#include <dithering_pars_fragment>
#include <color_pars_fragment>
#include <uv_pars_fragment>
#include <map_pars_fragment>
#include <alphamap_pars_fragment>
#include <alphatest_pars_fragment>
#include <alphahash_pars_fragment>
#include <aomap_pars_fragment>
#include <lightmap_pars_fragment>
#include <emissivemap_pars_fragment>
#include <envmap_common_pars_fragment>
#include <envmap_pars_fragment>
#include <fog_pars_fragment>
#include <bsdfs>
#include <lights_pars_begin>
#include <normal_pars_fragment>
#include <lights_phong_pars_fragment>
#include <shadowmap_pars_fragment>
#include <bumpmap_pars_fragment>
#include <normalmap_pars_fragment>
#include <specularmap_pars_fragment>
#include <logdepthbuf_pars_fragment>
#include <clipping_planes_pars_fragment>
void main() {
	vec4 diffuseColor = vec4( diffuse, opacity );
	#include <clipping_planes_fragment>
	ReflectedLight reflectedLight = ReflectedLight( vec3( 0.0 ), vec3( 0.0 ), vec3( 0.0 ), vec3( 0.0 ) );
	vec3 totalEmissiveRadiance = emissive;
	#include <logdepthbuf_fragment>
	#include <map_fragment>
	#include <color_fragment>
	#include <alphamap_fragment>
	#include <alphatest_fragment>
	#include <alphahash_fragment>
	#include <specularmap_fragment>
	#include <normal_fragment_begin>
	#include <normal_fragment_maps>
	#include <emissivemap_fragment>
	#include <lights_phong_fragment>
	#include <lights_fragment_begin>
	#include <lights_fragment_maps>
	#include <lights_fragment_end>
	#include <aomap_fragment>
	vec3 outgoingLight = reflectedLight.directDiffuse + reflectedLight.indirectDiffuse + reflectedLight.directSpecular + reflectedLight.indirectSpecular + totalEmissiveRadiance;
	#include <envmap_fragment>
	#include <opaque_fragment>
	#include <tonemapping_fragment>
	#include <colorspace_fragment>
	#include <fog_fragment>
	#include <premultiplied_alpha_fragment>
	#include <dithering_fragment>
}`,xx=`#define STANDARD
varying vec3 vViewPosition;
#ifdef USE_TRANSMISSION
	varying vec3 vWorldPosition;
#endif
#include <common>
#include <batching_pars_vertex>
#include <uv_pars_vertex>
#include <displacementmap_pars_vertex>
#include <color_pars_vertex>
#include <fog_pars_vertex>
#include <normal_pars_vertex>
#include <morphtarget_pars_vertex>
#include <skinning_pars_vertex>
#include <shadowmap_pars_vertex>
#include <logdepthbuf_pars_vertex>
#include <clipping_planes_pars_vertex>
void main() {
	#include <uv_vertex>
	#include <color_vertex>
	#include <morphinstance_vertex>
	#include <morphcolor_vertex>
	#include <batching_vertex>
	#include <beginnormal_vertex>
	#include <morphnormal_vertex>
	#include <skinbase_vertex>
	#include <skinnormal_vertex>
	#include <defaultnormal_vertex>
	#include <normal_vertex>
	#include <begin_vertex>
	#include <morphtarget_vertex>
	#include <skinning_vertex>
	#include <displacementmap_vertex>
	#include <project_vertex>
	#include <logdepthbuf_vertex>
	#include <clipping_planes_vertex>
	vViewPosition = - mvPosition.xyz;
	#include <worldpos_vertex>
	#include <shadowmap_vertex>
	#include <fog_vertex>
#ifdef USE_TRANSMISSION
	vWorldPosition = worldPosition.xyz;
#endif
}`,_x=`#define STANDARD
#ifdef PHYSICAL
	#define IOR
	#define USE_SPECULAR
#endif
uniform vec3 diffuse;
uniform vec3 emissive;
uniform float roughness;
uniform float metalness;
uniform float opacity;
#ifdef IOR
	uniform float ior;
#endif
#ifdef USE_SPECULAR
	uniform float specularIntensity;
	uniform vec3 specularColor;
	#ifdef USE_SPECULAR_COLORMAP
		uniform sampler2D specularColorMap;
	#endif
	#ifdef USE_SPECULAR_INTENSITYMAP
		uniform sampler2D specularIntensityMap;
	#endif
#endif
#ifdef USE_CLEARCOAT
	uniform float clearcoat;
	uniform float clearcoatRoughness;
#endif
#ifdef USE_DISPERSION
	uniform float dispersion;
#endif
#ifdef USE_IRIDESCENCE
	uniform float iridescence;
	uniform float iridescenceIOR;
	uniform float iridescenceThicknessMinimum;
	uniform float iridescenceThicknessMaximum;
#endif
#ifdef USE_SHEEN
	uniform vec3 sheenColor;
	uniform float sheenRoughness;
	#ifdef USE_SHEEN_COLORMAP
		uniform sampler2D sheenColorMap;
	#endif
	#ifdef USE_SHEEN_ROUGHNESSMAP
		uniform sampler2D sheenRoughnessMap;
	#endif
#endif
#ifdef USE_ANISOTROPY
	uniform vec2 anisotropyVector;
	#ifdef USE_ANISOTROPYMAP
		uniform sampler2D anisotropyMap;
	#endif
#endif
varying vec3 vViewPosition;
#include <common>
#include <packing>
#include <dithering_pars_fragment>
#include <color_pars_fragment>
#include <uv_pars_fragment>
#include <map_pars_fragment>
#include <alphamap_pars_fragment>
#include <alphatest_pars_fragment>
#include <alphahash_pars_fragment>
#include <aomap_pars_fragment>
#include <lightmap_pars_fragment>
#include <emissivemap_pars_fragment>
#include <iridescence_fragment>
#include <cube_uv_reflection_fragment>
#include <envmap_common_pars_fragment>
#include <envmap_physical_pars_fragment>
#include <fog_pars_fragment>
#include <lights_pars_begin>
#include <normal_pars_fragment>
#include <lights_physical_pars_fragment>
#include <transmission_pars_fragment>
#include <shadowmap_pars_fragment>
#include <bumpmap_pars_fragment>
#include <normalmap_pars_fragment>
#include <clearcoat_pars_fragment>
#include <iridescence_pars_fragment>
#include <roughnessmap_pars_fragment>
#include <metalnessmap_pars_fragment>
#include <logdepthbuf_pars_fragment>
#include <clipping_planes_pars_fragment>
void main() {
	vec4 diffuseColor = vec4( diffuse, opacity );
	#include <clipping_planes_fragment>
	ReflectedLight reflectedLight = ReflectedLight( vec3( 0.0 ), vec3( 0.0 ), vec3( 0.0 ), vec3( 0.0 ) );
	vec3 totalEmissiveRadiance = emissive;
	#include <logdepthbuf_fragment>
	#include <map_fragment>
	#include <color_fragment>
	#include <alphamap_fragment>
	#include <alphatest_fragment>
	#include <alphahash_fragment>
	#include <roughnessmap_fragment>
	#include <metalnessmap_fragment>
	#include <normal_fragment_begin>
	#include <normal_fragment_maps>
	#include <clearcoat_normal_fragment_begin>
	#include <clearcoat_normal_fragment_maps>
	#include <emissivemap_fragment>
	#include <lights_physical_fragment>
	#include <lights_fragment_begin>
	#include <lights_fragment_maps>
	#include <lights_fragment_end>
	#include <aomap_fragment>
	vec3 totalDiffuse = reflectedLight.directDiffuse + reflectedLight.indirectDiffuse;
	vec3 totalSpecular = reflectedLight.directSpecular + reflectedLight.indirectSpecular;
	#include <transmission_fragment>
	vec3 outgoingLight = totalDiffuse + totalSpecular + totalEmissiveRadiance;
	#ifdef USE_SHEEN
		float sheenEnergyComp = 1.0 - 0.157 * max3( material.sheenColor );
		outgoingLight = outgoingLight * sheenEnergyComp + sheenSpecularDirect + sheenSpecularIndirect;
	#endif
	#ifdef USE_CLEARCOAT
		float dotNVcc = saturate( dot( geometryClearcoatNormal, geometryViewDir ) );
		vec3 Fcc = F_Schlick( material.clearcoatF0, material.clearcoatF90, dotNVcc );
		outgoingLight = outgoingLight * ( 1.0 - material.clearcoat * Fcc ) + ( clearcoatSpecularDirect + clearcoatSpecularIndirect ) * material.clearcoat;
	#endif
	#include <opaque_fragment>
	#include <tonemapping_fragment>
	#include <colorspace_fragment>
	#include <fog_fragment>
	#include <premultiplied_alpha_fragment>
	#include <dithering_fragment>
}`,vx=`#define TOON
varying vec3 vViewPosition;
#include <common>
#include <batching_pars_vertex>
#include <uv_pars_vertex>
#include <displacementmap_pars_vertex>
#include <color_pars_vertex>
#include <fog_pars_vertex>
#include <normal_pars_vertex>
#include <morphtarget_pars_vertex>
#include <skinning_pars_vertex>
#include <shadowmap_pars_vertex>
#include <logdepthbuf_pars_vertex>
#include <clipping_planes_pars_vertex>
void main() {
	#include <uv_vertex>
	#include <color_vertex>
	#include <morphinstance_vertex>
	#include <morphcolor_vertex>
	#include <batching_vertex>
	#include <beginnormal_vertex>
	#include <morphnormal_vertex>
	#include <skinbase_vertex>
	#include <skinnormal_vertex>
	#include <defaultnormal_vertex>
	#include <normal_vertex>
	#include <begin_vertex>
	#include <morphtarget_vertex>
	#include <skinning_vertex>
	#include <displacementmap_vertex>
	#include <project_vertex>
	#include <logdepthbuf_vertex>
	#include <clipping_planes_vertex>
	vViewPosition = - mvPosition.xyz;
	#include <worldpos_vertex>
	#include <shadowmap_vertex>
	#include <fog_vertex>
}`,yx=`#define TOON
uniform vec3 diffuse;
uniform vec3 emissive;
uniform float opacity;
#include <common>
#include <packing>
#include <dithering_pars_fragment>
#include <color_pars_fragment>
#include <uv_pars_fragment>
#include <map_pars_fragment>
#include <alphamap_pars_fragment>
#include <alphatest_pars_fragment>
#include <alphahash_pars_fragment>
#include <aomap_pars_fragment>
#include <lightmap_pars_fragment>
#include <emissivemap_pars_fragment>
#include <gradientmap_pars_fragment>
#include <fog_pars_fragment>
#include <bsdfs>
#include <lights_pars_begin>
#include <normal_pars_fragment>
#include <lights_toon_pars_fragment>
#include <shadowmap_pars_fragment>
#include <bumpmap_pars_fragment>
#include <normalmap_pars_fragment>
#include <logdepthbuf_pars_fragment>
#include <clipping_planes_pars_fragment>
void main() {
	vec4 diffuseColor = vec4( diffuse, opacity );
	#include <clipping_planes_fragment>
	ReflectedLight reflectedLight = ReflectedLight( vec3( 0.0 ), vec3( 0.0 ), vec3( 0.0 ), vec3( 0.0 ) );
	vec3 totalEmissiveRadiance = emissive;
	#include <logdepthbuf_fragment>
	#include <map_fragment>
	#include <color_fragment>
	#include <alphamap_fragment>
	#include <alphatest_fragment>
	#include <alphahash_fragment>
	#include <normal_fragment_begin>
	#include <normal_fragment_maps>
	#include <emissivemap_fragment>
	#include <lights_toon_fragment>
	#include <lights_fragment_begin>
	#include <lights_fragment_maps>
	#include <lights_fragment_end>
	#include <aomap_fragment>
	vec3 outgoingLight = reflectedLight.directDiffuse + reflectedLight.indirectDiffuse + totalEmissiveRadiance;
	#include <opaque_fragment>
	#include <tonemapping_fragment>
	#include <colorspace_fragment>
	#include <fog_fragment>
	#include <premultiplied_alpha_fragment>
	#include <dithering_fragment>
}`,Mx=`uniform float size;
uniform float scale;
#include <common>
#include <color_pars_vertex>
#include <fog_pars_vertex>
#include <morphtarget_pars_vertex>
#include <logdepthbuf_pars_vertex>
#include <clipping_planes_pars_vertex>
#ifdef USE_POINTS_UV
	varying vec2 vUv;
	uniform mat3 uvTransform;
#endif
void main() {
	#ifdef USE_POINTS_UV
		vUv = ( uvTransform * vec3( uv, 1 ) ).xy;
	#endif
	#include <color_vertex>
	#include <morphinstance_vertex>
	#include <morphcolor_vertex>
	#include <begin_vertex>
	#include <morphtarget_vertex>
	#include <project_vertex>
	gl_PointSize = size;
	#ifdef USE_SIZEATTENUATION
		bool isPerspective = isPerspectiveMatrix( projectionMatrix );
		if ( isPerspective ) gl_PointSize *= ( scale / - mvPosition.z );
	#endif
	#include <logdepthbuf_vertex>
	#include <clipping_planes_vertex>
	#include <worldpos_vertex>
	#include <fog_vertex>
}`,Sx=`uniform vec3 diffuse;
uniform float opacity;
#include <common>
#include <color_pars_fragment>
#include <map_particle_pars_fragment>
#include <alphatest_pars_fragment>
#include <alphahash_pars_fragment>
#include <fog_pars_fragment>
#include <logdepthbuf_pars_fragment>
#include <clipping_planes_pars_fragment>
void main() {
	vec4 diffuseColor = vec4( diffuse, opacity );
	#include <clipping_planes_fragment>
	vec3 outgoingLight = vec3( 0.0 );
	#include <logdepthbuf_fragment>
	#include <map_particle_fragment>
	#include <color_fragment>
	#include <alphatest_fragment>
	#include <alphahash_fragment>
	outgoingLight = diffuseColor.rgb;
	#include <opaque_fragment>
	#include <tonemapping_fragment>
	#include <colorspace_fragment>
	#include <fog_fragment>
	#include <premultiplied_alpha_fragment>
}`,bx=`#include <common>
#include <batching_pars_vertex>
#include <fog_pars_vertex>
#include <morphtarget_pars_vertex>
#include <skinning_pars_vertex>
#include <logdepthbuf_pars_vertex>
#include <shadowmap_pars_vertex>
void main() {
	#include <batching_vertex>
	#include <beginnormal_vertex>
	#include <morphinstance_vertex>
	#include <morphnormal_vertex>
	#include <skinbase_vertex>
	#include <skinnormal_vertex>
	#include <defaultnormal_vertex>
	#include <begin_vertex>
	#include <morphtarget_vertex>
	#include <skinning_vertex>
	#include <project_vertex>
	#include <logdepthbuf_vertex>
	#include <worldpos_vertex>
	#include <shadowmap_vertex>
	#include <fog_vertex>
}`,Ex=`uniform vec3 color;
uniform float opacity;
#include <common>
#include <packing>
#include <fog_pars_fragment>
#include <bsdfs>
#include <lights_pars_begin>
#include <logdepthbuf_pars_fragment>
#include <shadowmap_pars_fragment>
#include <shadowmask_pars_fragment>
void main() {
	#include <logdepthbuf_fragment>
	gl_FragColor = vec4( color, opacity * ( 1.0 - getShadowMask() ) );
	#include <tonemapping_fragment>
	#include <colorspace_fragment>
	#include <fog_fragment>
}`,wx=`uniform float rotation;
uniform vec2 center;
#include <common>
#include <uv_pars_vertex>
#include <fog_pars_vertex>
#include <logdepthbuf_pars_vertex>
#include <clipping_planes_pars_vertex>
void main() {
	#include <uv_vertex>
	vec4 mvPosition = modelViewMatrix[ 3 ];
	vec2 scale = vec2( length( modelMatrix[ 0 ].xyz ), length( modelMatrix[ 1 ].xyz ) );
	#ifndef USE_SIZEATTENUATION
		bool isPerspective = isPerspectiveMatrix( projectionMatrix );
		if ( isPerspective ) scale *= - mvPosition.z;
	#endif
	vec2 alignedPosition = ( position.xy - ( center - vec2( 0.5 ) ) ) * scale;
	vec2 rotatedPosition;
	rotatedPosition.x = cos( rotation ) * alignedPosition.x - sin( rotation ) * alignedPosition.y;
	rotatedPosition.y = sin( rotation ) * alignedPosition.x + cos( rotation ) * alignedPosition.y;
	mvPosition.xy += rotatedPosition;
	gl_Position = projectionMatrix * mvPosition;
	#include <logdepthbuf_vertex>
	#include <clipping_planes_vertex>
	#include <fog_vertex>
}`,Tx=`uniform vec3 diffuse;
uniform float opacity;
#include <common>
#include <uv_pars_fragment>
#include <map_pars_fragment>
#include <alphamap_pars_fragment>
#include <alphatest_pars_fragment>
#include <alphahash_pars_fragment>
#include <fog_pars_fragment>
#include <logdepthbuf_pars_fragment>
#include <clipping_planes_pars_fragment>
void main() {
	vec4 diffuseColor = vec4( diffuse, opacity );
	#include <clipping_planes_fragment>
	vec3 outgoingLight = vec3( 0.0 );
	#include <logdepthbuf_fragment>
	#include <map_fragment>
	#include <alphamap_fragment>
	#include <alphatest_fragment>
	#include <alphahash_fragment>
	outgoingLight = diffuseColor.rgb;
	#include <opaque_fragment>
	#include <tonemapping_fragment>
	#include <colorspace_fragment>
	#include <fog_fragment>
}`,Nt={alphahash_fragment:Km,alphahash_pars_fragment:$m,alphamap_fragment:Zm,alphamap_pars_fragment:Jm,alphatest_fragment:Qm,alphatest_pars_fragment:jm,aomap_fragment:tg,aomap_pars_fragment:eg,batching_pars_vertex:ng,batching_vertex:ig,begin_vertex:sg,beginnormal_vertex:rg,bsdfs:og,iridescence_fragment:ag,bumpmap_pars_fragment:lg,clipping_planes_fragment:cg,clipping_planes_pars_fragment:hg,clipping_planes_pars_vertex:ug,clipping_planes_vertex:fg,color_fragment:dg,color_pars_fragment:pg,color_pars_vertex:mg,color_vertex:gg,common:xg,cube_uv_reflection_fragment:_g,defaultnormal_vertex:vg,displacementmap_pars_vertex:yg,displacementmap_vertex:Mg,emissivemap_fragment:Sg,emissivemap_pars_fragment:bg,colorspace_fragment:Eg,colorspace_pars_fragment:wg,envmap_fragment:Tg,envmap_common_pars_fragment:Ag,envmap_pars_fragment:Cg,envmap_pars_vertex:Rg,envmap_physical_pars_fragment:zg,envmap_vertex:Pg,fog_vertex:Ig,fog_pars_vertex:Lg,fog_fragment:Dg,fog_pars_fragment:Ug,gradientmap_pars_fragment:Ng,lightmap_pars_fragment:Fg,lights_lambert_fragment:Og,lights_lambert_pars_fragment:Bg,lights_pars_begin:Vg,lights_toon_fragment:kg,lights_toon_pars_fragment:Hg,lights_phong_fragment:Gg,lights_phong_pars_fragment:Wg,lights_physical_fragment:Xg,lights_physical_pars_fragment:qg,lights_fragment_begin:Yg,lights_fragment_maps:Kg,lights_fragment_end:$g,logdepthbuf_fragment:Zg,logdepthbuf_pars_fragment:Jg,logdepthbuf_pars_vertex:Qg,logdepthbuf_vertex:jg,map_fragment:t0,map_pars_fragment:e0,map_particle_fragment:n0,map_particle_pars_fragment:i0,metalnessmap_fragment:s0,metalnessmap_pars_fragment:r0,morphinstance_vertex:o0,morphcolor_vertex:a0,morphnormal_vertex:l0,morphtarget_pars_vertex:c0,morphtarget_vertex:h0,normal_fragment_begin:u0,normal_fragment_maps:f0,normal_pars_fragment:d0,normal_pars_vertex:p0,normal_vertex:m0,normalmap_pars_fragment:g0,clearcoat_normal_fragment_begin:x0,clearcoat_normal_fragment_maps:_0,clearcoat_pars_fragment:v0,iridescence_pars_fragment:y0,opaque_fragment:M0,packing:S0,premultiplied_alpha_fragment:b0,project_vertex:E0,dithering_fragment:w0,dithering_pars_fragment:T0,roughnessmap_fragment:A0,roughnessmap_pars_fragment:C0,shadowmap_pars_fragment:R0,shadowmap_pars_vertex:P0,shadowmap_vertex:I0,shadowmask_pars_fragment:L0,skinbase_vertex:D0,skinning_pars_vertex:U0,skinning_vertex:N0,skinnormal_vertex:F0,specularmap_fragment:O0,specularmap_pars_fragment:B0,tonemapping_fragment:V0,tonemapping_pars_fragment:z0,transmission_fragment:k0,transmission_pars_fragment:H0,uv_pars_fragment:G0,uv_pars_vertex:W0,uv_vertex:X0,worldpos_vertex:q0,background_vert:Y0,background_frag:K0,backgroundCube_vert:$0,backgroundCube_frag:Z0,cube_vert:J0,cube_frag:Q0,depth_vert:j0,depth_frag:tx,distanceRGBA_vert:ex,distanceRGBA_frag:nx,equirect_vert:ix,equirect_frag:sx,linedashed_vert:rx,linedashed_frag:ox,meshbasic_vert:ax,meshbasic_frag:lx,meshlambert_vert:cx,meshlambert_frag:hx,meshmatcap_vert:ux,meshmatcap_frag:fx,meshnormal_vert:dx,meshnormal_frag:px,meshphong_vert:mx,meshphong_frag:gx,meshphysical_vert:xx,meshphysical_frag:_x,meshtoon_vert:vx,meshtoon_frag:yx,points_vert:Mx,points_frag:Sx,shadow_vert:bx,shadow_frag:Ex,sprite_vert:wx,sprite_frag:Tx},nt={common:{diffuse:{value:new Gt(16777215)},opacity:{value:1},map:{value:null},mapTransform:{value:new Ft},alphaMap:{value:null},alphaMapTransform:{value:new Ft},alphaTest:{value:0}},specularmap:{specularMap:{value:null},specularMapTransform:{value:new Ft}},envmap:{envMap:{value:null},envMapRotation:{value:new Ft},flipEnvMap:{value:-1},reflectivity:{value:1},ior:{value:1.5},refractionRatio:{value:.98}},aomap:{aoMap:{value:null},aoMapIntensity:{value:1},aoMapTransform:{value:new Ft}},lightmap:{lightMap:{value:null},lightMapIntensity:{value:1},lightMapTransform:{value:new Ft}},bumpmap:{bumpMap:{value:null},bumpMapTransform:{value:new Ft},bumpScale:{value:1}},normalmap:{normalMap:{value:null},normalMapTransform:{value:new Ft},normalScale:{value:new Zt(1,1)}},displacementmap:{displacementMap:{value:null},displacementMapTransform:{value:new Ft},displacementScale:{value:1},displacementBias:{value:0}},emissivemap:{emissiveMap:{value:null},emissiveMapTransform:{value:new Ft}},metalnessmap:{metalnessMap:{value:null},metalnessMapTransform:{value:new Ft}},roughnessmap:{roughnessMap:{value:null},roughnessMapTransform:{value:new Ft}},gradientmap:{gradientMap:{value:null}},fog:{fogDensity:{value:25e-5},fogNear:{value:1},fogFar:{value:2e3},fogColor:{value:new Gt(16777215)}},lights:{ambientLightColor:{value:[]},lightProbe:{value:[]},directionalLights:{value:[],properties:{direction:{},color:{}}},directionalLightShadows:{value:[],properties:{shadowIntensity:1,shadowBias:{},shadowNormalBias:{},shadowRadius:{},shadowMapSize:{}}},directionalShadowMap:{value:[]},directionalShadowMatrix:{value:[]},spotLights:{value:[],properties:{color:{},position:{},direction:{},distance:{},coneCos:{},penumbraCos:{},decay:{}}},spotLightShadows:{value:[],properties:{shadowIntensity:1,shadowBias:{},shadowNormalBias:{},shadowRadius:{},shadowMapSize:{}}},spotLightMap:{value:[]},spotShadowMap:{value:[]},spotLightMatrix:{value:[]},pointLights:{value:[],properties:{color:{},position:{},decay:{},distance:{}}},pointLightShadows:{value:[],properties:{shadowIntensity:1,shadowBias:{},shadowNormalBias:{},shadowRadius:{},shadowMapSize:{},shadowCameraNear:{},shadowCameraFar:{}}},pointShadowMap:{value:[]},pointShadowMatrix:{value:[]},hemisphereLights:{value:[],properties:{direction:{},skyColor:{},groundColor:{}}},rectAreaLights:{value:[],properties:{color:{},position:{},width:{},height:{}}},ltc_1:{value:null},ltc_2:{value:null}},points:{diffuse:{value:new Gt(16777215)},opacity:{value:1},size:{value:1},scale:{value:1},map:{value:null},alphaMap:{value:null},alphaMapTransform:{value:new Ft},alphaTest:{value:0},uvTransform:{value:new Ft}},sprite:{diffuse:{value:new Gt(16777215)},opacity:{value:1},center:{value:new Zt(.5,.5)},rotation:{value:0},map:{value:null},mapTransform:{value:new Ft},alphaMap:{value:null},alphaMapTransform:{value:new Ft},alphaTest:{value:0}}},ln={basic:{uniforms:ye([nt.common,nt.specularmap,nt.envmap,nt.aomap,nt.lightmap,nt.fog]),vertexShader:Nt.meshbasic_vert,fragmentShader:Nt.meshbasic_frag},lambert:{uniforms:ye([nt.common,nt.specularmap,nt.envmap,nt.aomap,nt.lightmap,nt.emissivemap,nt.bumpmap,nt.normalmap,nt.displacementmap,nt.fog,nt.lights,{emissive:{value:new Gt(0)}}]),vertexShader:Nt.meshlambert_vert,fragmentShader:Nt.meshlambert_frag},phong:{uniforms:ye([nt.common,nt.specularmap,nt.envmap,nt.aomap,nt.lightmap,nt.emissivemap,nt.bumpmap,nt.normalmap,nt.displacementmap,nt.fog,nt.lights,{emissive:{value:new Gt(0)},specular:{value:new Gt(1118481)},shininess:{value:30}}]),vertexShader:Nt.meshphong_vert,fragmentShader:Nt.meshphong_frag},standard:{uniforms:ye([nt.common,nt.envmap,nt.aomap,nt.lightmap,nt.emissivemap,nt.bumpmap,nt.normalmap,nt.displacementmap,nt.roughnessmap,nt.metalnessmap,nt.fog,nt.lights,{emissive:{value:new Gt(0)},roughness:{value:1},metalness:{value:0},envMapIntensity:{value:1}}]),vertexShader:Nt.meshphysical_vert,fragmentShader:Nt.meshphysical_frag},toon:{uniforms:ye([nt.common,nt.aomap,nt.lightmap,nt.emissivemap,nt.bumpmap,nt.normalmap,nt.displacementmap,nt.gradientmap,nt.fog,nt.lights,{emissive:{value:new Gt(0)}}]),vertexShader:Nt.meshtoon_vert,fragmentShader:Nt.meshtoon_frag},matcap:{uniforms:ye([nt.common,nt.bumpmap,nt.normalmap,nt.displacementmap,nt.fog,{matcap:{value:null}}]),vertexShader:Nt.meshmatcap_vert,fragmentShader:Nt.meshmatcap_frag},points:{uniforms:ye([nt.points,nt.fog]),vertexShader:Nt.points_vert,fragmentShader:Nt.points_frag},dashed:{uniforms:ye([nt.common,nt.fog,{scale:{value:1},dashSize:{value:1},totalSize:{value:2}}]),vertexShader:Nt.linedashed_vert,fragmentShader:Nt.linedashed_frag},depth:{uniforms:ye([nt.common,nt.displacementmap]),vertexShader:Nt.depth_vert,fragmentShader:Nt.depth_frag},normal:{uniforms:ye([nt.common,nt.bumpmap,nt.normalmap,nt.displacementmap,{opacity:{value:1}}]),vertexShader:Nt.meshnormal_vert,fragmentShader:Nt.meshnormal_frag},sprite:{uniforms:ye([nt.sprite,nt.fog]),vertexShader:Nt.sprite_vert,fragmentShader:Nt.sprite_frag},background:{uniforms:{uvTransform:{value:new Ft},t2D:{value:null},backgroundIntensity:{value:1}},vertexShader:Nt.background_vert,fragmentShader:Nt.background_frag},backgroundCube:{uniforms:{envMap:{value:null},flipEnvMap:{value:-1},backgroundBlurriness:{value:0},backgroundIntensity:{value:1},backgroundRotation:{value:new Ft}},vertexShader:Nt.backgroundCube_vert,fragmentShader:Nt.backgroundCube_frag},cube:{uniforms:{tCube:{value:null},tFlip:{value:-1},opacity:{value:1}},vertexShader:Nt.cube_vert,fragmentShader:Nt.cube_frag},equirect:{uniforms:{tEquirect:{value:null}},vertexShader:Nt.equirect_vert,fragmentShader:Nt.equirect_frag},distanceRGBA:{uniforms:ye([nt.common,nt.displacementmap,{referencePosition:{value:new F},nearDistance:{value:1},farDistance:{value:1e3}}]),vertexShader:Nt.distanceRGBA_vert,fragmentShader:Nt.distanceRGBA_frag},shadow:{uniforms:ye([nt.lights,nt.fog,{color:{value:new Gt(0)},opacity:{value:1}}]),vertexShader:Nt.shadow_vert,fragmentShader:Nt.shadow_frag}};ln.physical={uniforms:ye([ln.standard.uniforms,{clearcoat:{value:0},clearcoatMap:{value:null},clearcoatMapTransform:{value:new Ft},clearcoatNormalMap:{value:null},clearcoatNormalMapTransform:{value:new Ft},clearcoatNormalScale:{value:new Zt(1,1)},clearcoatRoughness:{value:0},clearcoatRoughnessMap:{value:null},clearcoatRoughnessMapTransform:{value:new Ft},dispersion:{value:0},iridescence:{value:0},iridescenceMap:{value:null},iridescenceMapTransform:{value:new Ft},iridescenceIOR:{value:1.3},iridescenceThicknessMinimum:{value:100},iridescenceThicknessMaximum:{value:400},iridescenceThicknessMap:{value:null},iridescenceThicknessMapTransform:{value:new Ft},sheen:{value:0},sheenColor:{value:new Gt(0)},sheenColorMap:{value:null},sheenColorMapTransform:{value:new Ft},sheenRoughness:{value:1},sheenRoughnessMap:{value:null},sheenRoughnessMapTransform:{value:new Ft},transmission:{value:0},transmissionMap:{value:null},transmissionMapTransform:{value:new Ft},transmissionSamplerSize:{value:new Zt},transmissionSamplerMap:{value:null},thickness:{value:0},thicknessMap:{value:null},thicknessMapTransform:{value:new Ft},attenuationDistance:{value:0},attenuationColor:{value:new Gt(0)},specularColor:{value:new Gt(1,1,1)},specularColorMap:{value:null},specularColorMapTransform:{value:new Ft},specularIntensity:{value:1},specularIntensityMap:{value:null},specularIntensityMapTransform:{value:new Ft},anisotropyVector:{value:new Zt},anisotropyMap:{value:null},anisotropyMapTransform:{value:new Ft}}]),vertexShader:Nt.meshphysical_vert,fragmentShader:Nt.meshphysical_frag};var oo={r:0,b:0,g:0},oi=new wn,Ax=new le;function Cx(n,t,e,i,s,r,o){let a=new Gt(0),l=r===!0?0:1,c,h,d=null,p=0,m=null;function g(b){let S=b.isScene===!0?b.background:null;return S&&S.isTexture&&(S=(b.backgroundBlurriness>0?e:t).get(S)),S}function _(b){let S=!1,E=g(b);E===null?f(a,l):E&&E.isColor&&(f(E,1),S=!0);let C=n.xr.getEnvironmentBlendMode();C==="additive"?i.buffers.color.setClear(0,0,0,1,o):C==="alpha-blend"&&i.buffers.color.setClear(0,0,0,0,o),(n.autoClear||S)&&(i.buffers.depth.setTest(!0),i.buffers.depth.setMask(!0),i.buffers.color.setMask(!0),n.clear(n.autoClearColor,n.autoClearDepth,n.autoClearStencil))}function u(b,S){let E=g(S);E&&(E.isCubeTexture||E.mapping===Yo)?(h===void 0&&(h=new Fe(new Hs(1,1,1),new cn({name:"BackgroundCubeMaterial",uniforms:ps(ln.backgroundCube.uniforms),vertexShader:ln.backgroundCube.vertexShader,fragmentShader:ln.backgroundCube.fragmentShader,side:Re,depthTest:!1,depthWrite:!1,fog:!1})),h.geometry.deleteAttribute("normal"),h.geometry.deleteAttribute("uv"),h.onBeforeRender=function(C,A,T){this.matrixWorld.copyPosition(T.matrixWorld)},Object.defineProperty(h.material,"envMap",{get:function(){return this.uniforms.envMap.value}}),s.update(h)),oi.copy(S.backgroundRotation),oi.x*=-1,oi.y*=-1,oi.z*=-1,E.isCubeTexture&&E.isRenderTargetTexture===!1&&(oi.y*=-1,oi.z*=-1),h.material.uniforms.envMap.value=E,h.material.uniforms.flipEnvMap.value=E.isCubeTexture&&E.isRenderTargetTexture===!1?-1:1,h.material.uniforms.backgroundBlurriness.value=S.backgroundBlurriness,h.material.uniforms.backgroundIntensity.value=S.backgroundIntensity,h.material.uniforms.backgroundRotation.value.setFromMatrix4(Ax.makeRotationFromEuler(oi)),h.material.toneMapped=Yt.getTransfer(E.colorSpace)!==te,(d!==E||p!==E.version||m!==n.toneMapping)&&(h.material.needsUpdate=!0,d=E,p=E.version,m=n.toneMapping),h.layers.enableAll(),b.unshift(h,h.geometry,h.material,0,0,null)):E&&E.isTexture&&(c===void 0&&(c=new Fe(new Fo(2,2),new cn({name:"BackgroundMaterial",uniforms:ps(ln.background.uniforms),vertexShader:ln.background.vertexShader,fragmentShader:ln.background.fragmentShader,side:Xn,depthTest:!1,depthWrite:!1,fog:!1})),c.geometry.deleteAttribute("normal"),Object.defineProperty(c.material,"map",{get:function(){return this.uniforms.t2D.value}}),s.update(c)),c.material.uniforms.t2D.value=E,c.material.uniforms.backgroundIntensity.value=S.backgroundIntensity,c.material.toneMapped=Yt.getTransfer(E.colorSpace)!==te,E.matrixAutoUpdate===!0&&E.updateMatrix(),c.material.uniforms.uvTransform.value.copy(E.matrix),(d!==E||p!==E.version||m!==n.toneMapping)&&(c.material.needsUpdate=!0,d=E,p=E.version,m=n.toneMapping),c.layers.enableAll(),b.unshift(c,c.geometry,c.material,0,0,null))}function f(b,S){b.getRGB(oo,Jf(n)),i.buffers.color.setClear(oo.r,oo.g,oo.b,S,o)}return{getClearColor:function(){return a},setClearColor:function(b,S=1){a.set(b),l=S,f(a,l)},getClearAlpha:function(){return l},setClearAlpha:function(b){l=b,f(a,l)},render:_,addToRenderList:u}}function Rx(n,t){let e=n.getParameter(n.MAX_VERTEX_ATTRIBS),i={},s=p(null),r=s,o=!1;function a(x,y,V,U,H){let $=!1,k=d(U,V,y);r!==k&&(r=k,c(r.object)),$=m(x,U,V,H),$&&g(x,U,V,H),H!==null&&t.update(H,n.ELEMENT_ARRAY_BUFFER),($||o)&&(o=!1,E(x,y,V,U),H!==null&&n.bindBuffer(n.ELEMENT_ARRAY_BUFFER,t.get(H).buffer))}function l(){return n.createVertexArray()}function c(x){return n.bindVertexArray(x)}function h(x){return n.deleteVertexArray(x)}function d(x,y,V){let U=V.wireframe===!0,H=i[x.id];H===void 0&&(H={},i[x.id]=H);let $=H[y.id];$===void 0&&($={},H[y.id]=$);let k=$[U];return k===void 0&&(k=p(l()),$[U]=k),k}function p(x){let y=[],V=[],U=[];for(let H=0;H<e;H++)y[H]=0,V[H]=0,U[H]=0;return{geometry:null,program:null,wireframe:!1,newAttributes:y,enabledAttributes:V,attributeDivisors:U,object:x,attributes:{},index:null}}function m(x,y,V,U){let H=r.attributes,$=y.attributes,k=0,Z=V.getAttributes();for(let z in Z)if(Z[z].location>=0){let ot=H[z],pt=$[z];if(pt===void 0&&(z==="instanceMatrix"&&x.instanceMatrix&&(pt=x.instanceMatrix),z==="instanceColor"&&x.instanceColor&&(pt=x.instanceColor)),ot===void 0||ot.attribute!==pt||pt&&ot.data!==pt.data)return!0;k++}return r.attributesNum!==k||r.index!==U}function g(x,y,V,U){let H={},$=y.attributes,k=0,Z=V.getAttributes();for(let z in Z)if(Z[z].location>=0){let ot=$[z];ot===void 0&&(z==="instanceMatrix"&&x.instanceMatrix&&(ot=x.instanceMatrix),z==="instanceColor"&&x.instanceColor&&(ot=x.instanceColor));let pt={};pt.attribute=ot,ot&&ot.data&&(pt.data=ot.data),H[z]=pt,k++}r.attributes=H,r.attributesNum=k,r.index=U}function _(){let x=r.newAttributes;for(let y=0,V=x.length;y<V;y++)x[y]=0}function u(x){f(x,0)}function f(x,y){let V=r.newAttributes,U=r.enabledAttributes,H=r.attributeDivisors;V[x]=1,U[x]===0&&(n.enableVertexAttribArray(x),U[x]=1),H[x]!==y&&(n.vertexAttribDivisor(x,y),H[x]=y)}function b(){let x=r.newAttributes,y=r.enabledAttributes;for(let V=0,U=y.length;V<U;V++)y[V]!==x[V]&&(n.disableVertexAttribArray(V),y[V]=0)}function S(x,y,V,U,H,$,k){k===!0?n.vertexAttribIPointer(x,y,V,H,$):n.vertexAttribPointer(x,y,V,U,H,$)}function E(x,y,V,U){_();let H=U.attributes,$=V.getAttributes(),k=y.defaultAttributeValues;for(let Z in $){let z=$[Z];if(z.location>=0){let it=H[Z];if(it===void 0&&(Z==="instanceMatrix"&&x.instanceMatrix&&(it=x.instanceMatrix),Z==="instanceColor"&&x.instanceColor&&(it=x.instanceColor)),it!==void 0){let ot=it.normalized,pt=it.itemSize,Bt=t.get(it);if(Bt===void 0)continue;let Vt=Bt.buffer,X=Bt.type,J=Bt.bytesPerElement,ht=X===n.INT||X===n.UNSIGNED_INT||it.gpuType===Mc;if(it.isInterleavedBufferAttribute){let rt=it.data,bt=rt.stride,et=it.offset;if(rt.isInstancedInterleavedBuffer){for(let at=0;at<z.locationSize;at++)f(z.location+at,rt.meshPerAttribute);x.isInstancedMesh!==!0&&U._maxInstanceCount===void 0&&(U._maxInstanceCount=rt.meshPerAttribute*rt.count)}else for(let at=0;at<z.locationSize;at++)u(z.location+at);n.bindBuffer(n.ARRAY_BUFFER,Vt);for(let at=0;at<z.locationSize;at++)S(z.location+at,pt/z.locationSize,X,ot,bt*J,(et+pt/z.locationSize*at)*J,ht)}else{if(it.isInstancedBufferAttribute){for(let rt=0;rt<z.locationSize;rt++)f(z.location+rt,it.meshPerAttribute);x.isInstancedMesh!==!0&&U._maxInstanceCount===void 0&&(U._maxInstanceCount=it.meshPerAttribute*it.count)}else for(let rt=0;rt<z.locationSize;rt++)u(z.location+rt);n.bindBuffer(n.ARRAY_BUFFER,Vt);for(let rt=0;rt<z.locationSize;rt++)S(z.location+rt,pt/z.locationSize,X,ot,pt*J,pt/z.locationSize*rt*J,ht)}}else if(k!==void 0){let ot=k[Z];if(ot!==void 0)switch(ot.length){case 2:n.vertexAttrib2fv(z.location,ot);break;case 3:n.vertexAttrib3fv(z.location,ot);break;case 4:n.vertexAttrib4fv(z.location,ot);break;default:n.vertexAttrib1fv(z.location,ot)}}}}b()}function C(){P();for(let x in i){let y=i[x];for(let V in y){let U=y[V];for(let H in U)h(U[H].object),delete U[H];delete y[V]}delete i[x]}}function A(x){if(i[x.id]===void 0)return;let y=i[x.id];for(let V in y){let U=y[V];for(let H in U)h(U[H].object),delete U[H];delete y[V]}delete i[x.id]}function T(x){for(let y in i){let V=i[y];if(V[x.id]===void 0)continue;let U=V[x.id];for(let H in U)h(U[H].object),delete U[H];delete V[x.id]}}function P(){G(),o=!0,r!==s&&(r=s,c(r.object))}function G(){s.geometry=null,s.program=null,s.wireframe=!1}return{setup:a,reset:P,resetDefaultState:G,dispose:C,releaseStatesOfGeometry:A,releaseStatesOfProgram:T,initAttributes:_,enableAttribute:u,disableUnusedAttributes:b}}function Px(n,t,e){let i;function s(c){i=c}function r(c,h){n.drawArrays(i,c,h),e.update(h,i,1)}function o(c,h,d){d!==0&&(n.drawArraysInstanced(i,c,h,d),e.update(h,i,d))}function a(c,h,d){if(d===0)return;t.get("WEBGL_multi_draw").multiDrawArraysWEBGL(i,c,0,h,0,d);let m=0;for(let g=0;g<d;g++)m+=h[g];e.update(m,i,1)}function l(c,h,d,p){if(d===0)return;let m=t.get("WEBGL_multi_draw");if(m===null)for(let g=0;g<c.length;g++)o(c[g],h[g],p[g]);else{m.multiDrawArraysInstancedWEBGL(i,c,0,h,0,p,0,d);let g=0;for(let _=0;_<d;_++)g+=h[_];for(let _=0;_<p.length;_++)e.update(g,i,p[_])}}this.setMode=s,this.render=r,this.renderInstances=o,this.renderMultiDraw=a,this.renderMultiDrawInstances=l}function Ix(n,t,e,i){let s;function r(){if(s!==void 0)return s;if(t.has("EXT_texture_filter_anisotropic")===!0){let T=t.get("EXT_texture_filter_anisotropic");s=n.getParameter(T.MAX_TEXTURE_MAX_ANISOTROPY_EXT)}else s=0;return s}function o(T){return!(T!==je&&i.convert(T)!==n.getParameter(n.IMPLEMENTATION_COLOR_READ_FORMAT))}function a(T){let P=T===Xs&&(t.has("EXT_color_buffer_half_float")||t.has("EXT_color_buffer_float"));return!(T!==bn&&i.convert(T)!==n.getParameter(n.IMPLEMENTATION_COLOR_READ_TYPE)&&T!==Mn&&!P)}function l(T){if(T==="highp"){if(n.getShaderPrecisionFormat(n.VERTEX_SHADER,n.HIGH_FLOAT).precision>0&&n.getShaderPrecisionFormat(n.FRAGMENT_SHADER,n.HIGH_FLOAT).precision>0)return"highp";T="mediump"}return T==="mediump"&&n.getShaderPrecisionFormat(n.VERTEX_SHADER,n.MEDIUM_FLOAT).precision>0&&n.getShaderPrecisionFormat(n.FRAGMENT_SHADER,n.MEDIUM_FLOAT).precision>0?"mediump":"lowp"}let c=e.precision!==void 0?e.precision:"highp",h=l(c);h!==c&&(console.warn("THREE.WebGLRenderer:",c,"not supported, using",h,"instead."),c=h);let d=e.logarithmicDepthBuffer===!0,p=e.reverseDepthBuffer===!0&&t.has("EXT_clip_control");if(p===!0){let T=t.get("EXT_clip_control");T.clipControlEXT(T.LOWER_LEFT_EXT,T.ZERO_TO_ONE_EXT)}let m=n.getParameter(n.MAX_TEXTURE_IMAGE_UNITS),g=n.getParameter(n.MAX_VERTEX_TEXTURE_IMAGE_UNITS),_=n.getParameter(n.MAX_TEXTURE_SIZE),u=n.getParameter(n.MAX_CUBE_MAP_TEXTURE_SIZE),f=n.getParameter(n.MAX_VERTEX_ATTRIBS),b=n.getParameter(n.MAX_VERTEX_UNIFORM_VECTORS),S=n.getParameter(n.MAX_VARYING_VECTORS),E=n.getParameter(n.MAX_FRAGMENT_UNIFORM_VECTORS),C=g>0,A=n.getParameter(n.MAX_SAMPLES);return{isWebGL2:!0,getMaxAnisotropy:r,getMaxPrecision:l,textureFormatReadable:o,textureTypeReadable:a,precision:c,logarithmicDepthBuffer:d,reverseDepthBuffer:p,maxTextures:m,maxVertexTextures:g,maxTextureSize:_,maxCubemapSize:u,maxAttributes:f,maxVertexUniforms:b,maxVaryings:S,maxFragmentUniforms:E,vertexTextures:C,maxSamples:A}}function Lx(n){let t=this,e=null,i=0,s=!1,r=!1,o=new yn,a=new Ft,l={value:null,needsUpdate:!1};this.uniform=l,this.numPlanes=0,this.numIntersection=0,this.init=function(d,p){let m=d.length!==0||p||i!==0||s;return s=p,i=d.length,m},this.beginShadows=function(){r=!0,h(null)},this.endShadows=function(){r=!1},this.setGlobalState=function(d,p){e=h(d,p,0)},this.setState=function(d,p,m){let g=d.clippingPlanes,_=d.clipIntersection,u=d.clipShadows,f=n.get(d);if(!s||g===null||g.length===0||r&&!u)r?h(null):c();else{let b=r?0:i,S=b*4,E=f.clippingState||null;l.value=E,E=h(g,p,S,m);for(let C=0;C!==S;++C)E[C]=e[C];f.clippingState=E,this.numIntersection=_?this.numPlanes:0,this.numPlanes+=b}};function c(){l.value!==e&&(l.value=e,l.needsUpdate=i>0),t.numPlanes=i,t.numIntersection=0}function h(d,p,m,g){let _=d!==null?d.length:0,u=null;if(_!==0){if(u=l.value,g!==!0||u===null){let f=m+_*4,b=p.matrixWorldInverse;a.getNormalMatrix(b),(u===null||u.length<f)&&(u=new Float32Array(f));for(let S=0,E=m;S!==_;++S,E+=4)o.copy(d[S]).applyMatrix4(b,a),o.normal.toArray(u,E),u[E+3]=o.constant}l.value=u,l.needsUpdate=!0}return t.numPlanes=_,t.numIntersection=0,u}}function Dx(n){let t=new WeakMap;function e(o,a){return a===dl?o.mapping=cs:a===pl&&(o.mapping=hs),o}function i(o){if(o&&o.isTexture){let a=o.mapping;if(a===dl||a===pl)if(t.has(o)){let l=t.get(o).texture;return e(l,o.mapping)}else{let l=o.image;if(l&&l.height>0){let c=new $l(l.height);return c.fromEquirectangularTexture(n,o),t.set(o,c),o.addEventListener("dispose",s),e(c.texture,o.mapping)}else return null}}return o}function s(o){let a=o.target;a.removeEventListener("dispose",s);let l=t.get(a);l!==void 0&&(t.delete(a),l.dispose())}function r(){t=new WeakMap}return{get:i,dispose:r}}var Zl=class extends Do{constructor(t=-1,e=1,i=1,s=-1,r=.1,o=2e3){super(),this.isOrthographicCamera=!0,this.type="OrthographicCamera",this.zoom=1,this.view=null,this.left=t,this.right=e,this.top=i,this.bottom=s,this.near=r,this.far=o,this.updateProjectionMatrix()}copy(t,e){return super.copy(t,e),this.left=t.left,this.right=t.right,this.top=t.top,this.bottom=t.bottom,this.near=t.near,this.far=t.far,this.zoom=t.zoom,this.view=t.view===null?null:Object.assign({},t.view),this}setViewOffset(t,e,i,s,r,o){this.view===null&&(this.view={enabled:!0,fullWidth:1,fullHeight:1,offsetX:0,offsetY:0,width:1,height:1}),this.view.enabled=!0,this.view.fullWidth=t,this.view.fullHeight=e,this.view.offsetX=i,this.view.offsetY=s,this.view.width=r,this.view.height=o,this.updateProjectionMatrix()}clearViewOffset(){this.view!==null&&(this.view.enabled=!1),this.updateProjectionMatrix()}updateProjectionMatrix(){let t=(this.right-this.left)/(2*this.zoom),e=(this.top-this.bottom)/(2*this.zoom),i=(this.right+this.left)/2,s=(this.top+this.bottom)/2,r=i-t,o=i+t,a=s+e,l=s-e;if(this.view!==null&&this.view.enabled){let c=(this.right-this.left)/this.view.fullWidth/this.zoom,h=(this.top-this.bottom)/this.view.fullHeight/this.zoom;r+=c*this.view.offsetX,o=r+c*this.view.width,a-=h*this.view.offsetY,l=a-h*this.view.height}this.projectionMatrix.makeOrthographic(r,o,a,l,this.near,this.far,this.coordinateSystem),this.projectionMatrixInverse.copy(this.projectionMatrix).invert()}toJSON(t){let e=super.toJSON(t);return e.object.zoom=this.zoom,e.object.left=this.left,e.object.right=this.right,e.object.top=this.top,e.object.bottom=this.bottom,e.object.near=this.near,e.object.far=this.far,this.view!==null&&(e.object.view=Object.assign({},this.view)),e}},ns=4,rf=[.125,.215,.35,.446,.526,.582],ui=20,Ja=new Zl,of=new Gt,Qa=null,ja=0,tl=0,el=!1,li=(1+Math.sqrt(5))/2,es=1/li,af=[new F(-li,es,0),new F(li,es,0),new F(-es,0,li),new F(es,0,li),new F(0,li,-es),new F(0,li,es),new F(-1,1,-1),new F(1,1,-1),new F(-1,1,1),new F(1,1,1)],Oo=class{constructor(t){this._renderer=t,this._pingPongRenderTarget=null,this._lodMax=0,this._cubeSize=0,this._lodPlanes=[],this._sizeLods=[],this._sigmas=[],this._blurMaterial=null,this._cubemapMaterial=null,this._equirectMaterial=null,this._compileMaterial(this._blurMaterial)}fromScene(t,e=0,i=.1,s=100){Qa=this._renderer.getRenderTarget(),ja=this._renderer.getActiveCubeFace(),tl=this._renderer.getActiveMipmapLevel(),el=this._renderer.xr.enabled,this._renderer.xr.enabled=!1,this._setSize(256);let r=this._allocateTargets();return r.depthBuffer=!0,this._sceneToCubeUV(t,i,s,r),e>0&&this._blur(r,0,0,e),this._applyPMREM(r),this._cleanup(r),r}fromEquirectangular(t,e=null){return this._fromTexture(t,e)}fromCubemap(t,e=null){return this._fromTexture(t,e)}compileCubemapShader(){this._cubemapMaterial===null&&(this._cubemapMaterial=hf(),this._compileMaterial(this._cubemapMaterial))}compileEquirectangularShader(){this._equirectMaterial===null&&(this._equirectMaterial=cf(),this._compileMaterial(this._equirectMaterial))}dispose(){this._dispose(),this._cubemapMaterial!==null&&this._cubemapMaterial.dispose(),this._equirectMaterial!==null&&this._equirectMaterial.dispose()}_setSize(t){this._lodMax=Math.floor(Math.log2(t)),this._cubeSize=Math.pow(2,this._lodMax)}_dispose(){this._blurMaterial!==null&&this._blurMaterial.dispose(),this._pingPongRenderTarget!==null&&this._pingPongRenderTarget.dispose();for(let t=0;t<this._lodPlanes.length;t++)this._lodPlanes[t].dispose()}_cleanup(t){this._renderer.setRenderTarget(Qa,ja,tl),this._renderer.xr.enabled=el,t.scissorTest=!1,ao(t,0,0,t.width,t.height)}_fromTexture(t,e){t.mapping===cs||t.mapping===hs?this._setSize(t.image.length===0?16:t.image[0].width||t.image[0].image.width):this._setSize(t.image.width/4),Qa=this._renderer.getRenderTarget(),ja=this._renderer.getActiveCubeFace(),tl=this._renderer.getActiveMipmapLevel(),el=this._renderer.xr.enabled,this._renderer.xr.enabled=!1;let i=e||this._allocateTargets();return this._textureToCubeUV(t,i),this._applyPMREM(i),this._cleanup(i),i}_allocateTargets(){let t=3*Math.max(this._cubeSize,112),e=4*this._cubeSize,i={magFilter:Qe,minFilter:Qe,generateMipmaps:!1,type:Xs,format:je,colorSpace:$n,depthBuffer:!1},s=lf(t,e,i);if(this._pingPongRenderTarget===null||this._pingPongRenderTarget.width!==t||this._pingPongRenderTarget.height!==e){this._pingPongRenderTarget!==null&&this._dispose(),this._pingPongRenderTarget=lf(t,e,i);let{_lodMax:r}=this;({sizeLods:this._sizeLods,lodPlanes:this._lodPlanes,sigmas:this._sigmas}=Ux(r)),this._blurMaterial=Nx(r,t,e)}return s}_compileMaterial(t){let e=new Fe(this._lodPlanes[0],t);this._renderer.compile(e,Ja)}_sceneToCubeUV(t,e,i,s){let a=new Me(90,1,e,i),l=[1,-1,1,1,1,1],c=[1,1,1,-1,-1,-1],h=this._renderer,d=h.autoClear,p=h.toneMapping;h.getClearColor(of),h.toneMapping=Wn,h.autoClear=!1;let m=new ds({name:"PMREM.Background",side:Re,depthWrite:!1,depthTest:!1}),g=new Fe(new Hs,m),_=!1,u=t.background;u?u.isColor&&(m.color.copy(u),t.background=null,_=!0):(m.color.copy(of),_=!0);for(let f=0;f<6;f++){let b=f%3;b===0?(a.up.set(0,l[f],0),a.lookAt(c[f],0,0)):b===1?(a.up.set(0,0,l[f]),a.lookAt(0,c[f],0)):(a.up.set(0,l[f],0),a.lookAt(0,0,c[f]));let S=this._cubeSize;ao(s,b*S,f>2?S:0,S,S),h.setRenderTarget(s),_&&h.render(g,a),h.render(t,a)}g.geometry.dispose(),g.material.dispose(),h.toneMapping=p,h.autoClear=d,t.background=u}_textureToCubeUV(t,e){let i=this._renderer,s=t.mapping===cs||t.mapping===hs;s?(this._cubemapMaterial===null&&(this._cubemapMaterial=hf()),this._cubemapMaterial.uniforms.flipEnvMap.value=t.isRenderTargetTexture===!1?-1:1):this._equirectMaterial===null&&(this._equirectMaterial=cf());let r=s?this._cubemapMaterial:this._equirectMaterial,o=new Fe(this._lodPlanes[0],r),a=r.uniforms;a.envMap.value=t;let l=this._cubeSize;ao(e,0,0,3*l,2*l),i.setRenderTarget(e),i.render(o,Ja)}_applyPMREM(t){let e=this._renderer,i=e.autoClear;e.autoClear=!1;let s=this._lodPlanes.length;for(let r=1;r<s;r++){let o=Math.sqrt(this._sigmas[r]*this._sigmas[r]-this._sigmas[r-1]*this._sigmas[r-1]),a=af[(s-r-1)%af.length];this._blur(t,r-1,r,o,a)}e.autoClear=i}_blur(t,e,i,s,r){let o=this._pingPongRenderTarget;this._halfBlur(t,o,e,i,s,"latitudinal",r),this._halfBlur(o,t,i,i,s,"longitudinal",r)}_halfBlur(t,e,i,s,r,o,a){let l=this._renderer,c=this._blurMaterial;o!=="latitudinal"&&o!=="longitudinal"&&console.error("blur direction must be either latitudinal or longitudinal!");let h=3,d=new Fe(this._lodPlanes[s],c),p=c.uniforms,m=this._sizeLods[i]-1,g=isFinite(r)?Math.PI/(2*m):2*Math.PI/(2*ui-1),_=r/g,u=isFinite(r)?1+Math.floor(h*_):ui;u>ui&&console.warn(`sigmaRadians, ${r}, is too large and will clip, as it requested ${u} samples when the maximum is set to ${ui}`);let f=[],b=0;for(let T=0;T<ui;++T){let P=T/_,G=Math.exp(-P*P/2);f.push(G),T===0?b+=G:T<u&&(b+=2*G)}for(let T=0;T<f.length;T++)f[T]=f[T]/b;p.envMap.value=t.texture,p.samples.value=u,p.weights.value=f,p.latitudinal.value=o==="latitudinal",a&&(p.poleAxis.value=a);let{_lodMax:S}=this;p.dTheta.value=g,p.mipInt.value=S-i;let E=this._sizeLods[s],C=3*E*(s>S-ns?s-S+ns:0),A=4*(this._cubeSize-E);ao(e,C,A,3*E,2*E),l.setRenderTarget(e),l.render(d,Ja)}};function Ux(n){let t=[],e=[],i=[],s=n,r=n-ns+1+rf.length;for(let o=0;o<r;o++){let a=Math.pow(2,s);e.push(a);let l=1/a;o>n-ns?l=rf[o-n+ns-1]:o===0&&(l=0),i.push(l);let c=1/(a-2),h=-c,d=1+c,p=[h,h,d,h,d,d,h,h,d,d,h,d],m=6,g=6,_=3,u=2,f=1,b=new Float32Array(_*g*m),S=new Float32Array(u*g*m),E=new Float32Array(f*g*m);for(let A=0;A<m;A++){let T=A%3*2/3-1,P=A>2?0:-1,G=[T,P,0,T+2/3,P,0,T+2/3,P+1,0,T,P,0,T+2/3,P+1,0,T,P+1,0];b.set(G,_*g*A),S.set(p,u*g*A);let x=[A,A,A,A,A,A];E.set(x,f*g*A)}let C=new Be;C.setAttribute("position",new Se(b,_)),C.setAttribute("uv",new Se(S,u)),C.setAttribute("faceIndex",new Se(E,f)),t.push(C),s>ns&&s--}return{lodPlanes:t,sizeLods:e,sigmas:i}}function lf(n,t,e){let i=new En(n,t,e);return i.texture.mapping=Yo,i.texture.name="PMREM.cubeUv",i.scissorTest=!0,i}function ao(n,t,e,i,s){n.viewport.set(t,e,i,s),n.scissor.set(t,e,i,s)}function Nx(n,t,e){let i=new Float32Array(ui),s=new F(0,1,0);return new cn({name:"SphericalGaussianBlur",defines:{n:ui,CUBEUV_TEXEL_WIDTH:1/t,CUBEUV_TEXEL_HEIGHT:1/e,CUBEUV_MAX_MIP:`${n}.0`},uniforms:{envMap:{value:null},samples:{value:1},weights:{value:i},latitudinal:{value:!1},dTheta:{value:0},mipInt:{value:0},poleAxis:{value:s}},vertexShader:Cc(),fragmentShader:`

			precision mediump float;
			precision mediump int;

			varying vec3 vOutputDirection;

			uniform sampler2D envMap;
			uniform int samples;
			uniform float weights[ n ];
			uniform bool latitudinal;
			uniform float dTheta;
			uniform float mipInt;
			uniform vec3 poleAxis;

			#define ENVMAP_TYPE_CUBE_UV
			#include <cube_uv_reflection_fragment>

			vec3 getSample( float theta, vec3 axis ) {

				float cosTheta = cos( theta );
				// Rodrigues' axis-angle rotation
				vec3 sampleDirection = vOutputDirection * cosTheta
					+ cross( axis, vOutputDirection ) * sin( theta )
					+ axis * dot( axis, vOutputDirection ) * ( 1.0 - cosTheta );

				return bilinearCubeUV( envMap, sampleDirection, mipInt );

			}

			void main() {

				vec3 axis = latitudinal ? poleAxis : cross( poleAxis, vOutputDirection );

				if ( all( equal( axis, vec3( 0.0 ) ) ) ) {

					axis = vec3( vOutputDirection.z, 0.0, - vOutputDirection.x );

				}

				axis = normalize( axis );

				gl_FragColor = vec4( 0.0, 0.0, 0.0, 1.0 );
				gl_FragColor.rgb += weights[ 0 ] * getSample( 0.0, axis );

				for ( int i = 1; i < n; i++ ) {

					if ( i >= samples ) {

						break;

					}

					float theta = dTheta * float( i );
					gl_FragColor.rgb += weights[ i ] * getSample( -1.0 * theta, axis );
					gl_FragColor.rgb += weights[ i ] * getSample( theta, axis );

				}

			}
		`,blending:Gn,depthTest:!1,depthWrite:!1})}function cf(){return new cn({name:"EquirectangularToCubeUV",uniforms:{envMap:{value:null}},vertexShader:Cc(),fragmentShader:`

			precision mediump float;
			precision mediump int;

			varying vec3 vOutputDirection;

			uniform sampler2D envMap;

			#include <common>

			void main() {

				vec3 outputDirection = normalize( vOutputDirection );
				vec2 uv = equirectUv( outputDirection );

				gl_FragColor = vec4( texture2D ( envMap, uv ).rgb, 1.0 );

			}
		`,blending:Gn,depthTest:!1,depthWrite:!1})}function hf(){return new cn({name:"CubemapToCubeUV",uniforms:{envMap:{value:null},flipEnvMap:{value:-1}},vertexShader:Cc(),fragmentShader:`

			precision mediump float;
			precision mediump int;

			uniform float flipEnvMap;

			varying vec3 vOutputDirection;

			uniform samplerCube envMap;

			void main() {

				gl_FragColor = textureCube( envMap, vec3( flipEnvMap * vOutputDirection.x, vOutputDirection.yz ) );

			}
		`,blending:Gn,depthTest:!1,depthWrite:!1})}function Cc(){return`

		precision mediump float;
		precision mediump int;

		attribute float faceIndex;

		varying vec3 vOutputDirection;

		// RH coordinate system; PMREM face-indexing convention
		vec3 getDirection( vec2 uv, float face ) {

			uv = 2.0 * uv - 1.0;

			vec3 direction = vec3( uv, 1.0 );

			if ( face == 0.0 ) {

				direction = direction.zyx; // ( 1, v, u ) pos x

			} else if ( face == 1.0 ) {

				direction = direction.xzy;
				direction.xz *= -1.0; // ( -u, 1, -v ) pos y

			} else if ( face == 2.0 ) {

				direction.x *= -1.0; // ( -u, v, 1 ) pos z

			} else if ( face == 3.0 ) {

				direction = direction.zyx;
				direction.xz *= -1.0; // ( -1, v, -u ) neg x

			} else if ( face == 4.0 ) {

				direction = direction.xzy;
				direction.xy *= -1.0; // ( -u, -1, v ) neg y

			} else if ( face == 5.0 ) {

				direction.z *= -1.0; // ( u, v, -1 ) neg z

			}

			return direction;

		}

		void main() {

			vOutputDirection = getDirection( uv, faceIndex );
			gl_Position = vec4( position, 1.0 );

		}
	`}function Fx(n){let t=new WeakMap,e=null;function i(a){if(a&&a.isTexture){let l=a.mapping,c=l===dl||l===pl,h=l===cs||l===hs;if(c||h){let d=t.get(a),p=d!==void 0?d.texture.pmremVersion:0;if(a.isRenderTargetTexture&&a.pmremVersion!==p)return e===null&&(e=new Oo(n)),d=c?e.fromEquirectangular(a,d):e.fromCubemap(a,d),d.texture.pmremVersion=a.pmremVersion,t.set(a,d),d.texture;if(d!==void 0)return d.texture;{let m=a.image;return c&&m&&m.height>0||h&&m&&s(m)?(e===null&&(e=new Oo(n)),d=c?e.fromEquirectangular(a):e.fromCubemap(a),d.texture.pmremVersion=a.pmremVersion,t.set(a,d),a.addEventListener("dispose",r),d.texture):null}}}return a}function s(a){let l=0,c=6;for(let h=0;h<c;h++)a[h]!==void 0&&l++;return l===c}function r(a){let l=a.target;l.removeEventListener("dispose",r);let c=t.get(l);c!==void 0&&(t.delete(l),c.dispose())}function o(){t=new WeakMap,e!==null&&(e.dispose(),e=null)}return{get:i,dispose:o}}function Ox(n){let t={};function e(i){if(t[i]!==void 0)return t[i];let s;switch(i){case"WEBGL_depth_texture":s=n.getExtension("WEBGL_depth_texture")||n.getExtension("MOZ_WEBGL_depth_texture")||n.getExtension("WEBKIT_WEBGL_depth_texture");break;case"EXT_texture_filter_anisotropic":s=n.getExtension("EXT_texture_filter_anisotropic")||n.getExtension("MOZ_EXT_texture_filter_anisotropic")||n.getExtension("WEBKIT_EXT_texture_filter_anisotropic");break;case"WEBGL_compressed_texture_s3tc":s=n.getExtension("WEBGL_compressed_texture_s3tc")||n.getExtension("MOZ_WEBGL_compressed_texture_s3tc")||n.getExtension("WEBKIT_WEBGL_compressed_texture_s3tc");break;case"WEBGL_compressed_texture_pvrtc":s=n.getExtension("WEBGL_compressed_texture_pvrtc")||n.getExtension("WEBKIT_WEBGL_compressed_texture_pvrtc");break;default:s=n.getExtension(i)}return t[i]=s,s}return{has:function(i){return e(i)!==null},init:function(){e("EXT_color_buffer_float"),e("WEBGL_clip_cull_distance"),e("OES_texture_float_linear"),e("EXT_color_buffer_half_float"),e("WEBGL_multisampled_render_to_texture"),e("WEBGL_render_shared_exponent")},get:function(i){let s=e(i);return s===null&&yo("THREE.WebGLRenderer: "+i+" extension not supported."),s}}}function Bx(n,t,e,i){let s={},r=new WeakMap;function o(d){let p=d.target;p.index!==null&&t.remove(p.index);for(let g in p.attributes)t.remove(p.attributes[g]);for(let g in p.morphAttributes){let _=p.morphAttributes[g];for(let u=0,f=_.length;u<f;u++)t.remove(_[u])}p.removeEventListener("dispose",o),delete s[p.id];let m=r.get(p);m&&(t.remove(m),r.delete(p)),i.releaseStatesOfGeometry(p),p.isInstancedBufferGeometry===!0&&delete p._maxInstanceCount,e.memory.geometries--}function a(d,p){return s[p.id]===!0||(p.addEventListener("dispose",o),s[p.id]=!0,e.memory.geometries++),p}function l(d){let p=d.attributes;for(let g in p)t.update(p[g],n.ARRAY_BUFFER);let m=d.morphAttributes;for(let g in m){let _=m[g];for(let u=0,f=_.length;u<f;u++)t.update(_[u],n.ARRAY_BUFFER)}}function c(d){let p=[],m=d.index,g=d.attributes.position,_=0;if(m!==null){let b=m.array;_=m.version;for(let S=0,E=b.length;S<E;S+=3){let C=b[S+0],A=b[S+1],T=b[S+2];p.push(C,A,A,T,T,C)}}else if(g!==void 0){let b=g.array;_=g.version;for(let S=0,E=b.length/3-1;S<E;S+=3){let C=S+0,A=S+1,T=S+2;p.push(C,A,A,T,T,C)}}else return;let u=new($f(p)?Lo:Io)(p,1);u.version=_;let f=r.get(d);f&&t.remove(f),r.set(d,u)}function h(d){let p=r.get(d);if(p){let m=d.index;m!==null&&p.version<m.version&&c(d)}else c(d);return r.get(d)}return{get:a,update:l,getWireframeAttribute:h}}function Vx(n,t,e){let i;function s(p){i=p}let r,o;function a(p){r=p.type,o=p.bytesPerElement}function l(p,m){n.drawElements(i,m,r,p*o),e.update(m,i,1)}function c(p,m,g){g!==0&&(n.drawElementsInstanced(i,m,r,p*o,g),e.update(m,i,g))}function h(p,m,g){if(g===0)return;t.get("WEBGL_multi_draw").multiDrawElementsWEBGL(i,m,0,r,p,0,g);let u=0;for(let f=0;f<g;f++)u+=m[f];e.update(u,i,1)}function d(p,m,g,_){if(g===0)return;let u=t.get("WEBGL_multi_draw");if(u===null)for(let f=0;f<p.length;f++)c(p[f]/o,m[f],_[f]);else{u.multiDrawElementsInstancedWEBGL(i,m,0,r,p,0,_,0,g);let f=0;for(let b=0;b<g;b++)f+=m[b];for(let b=0;b<_.length;b++)e.update(f,i,_[b])}}this.setMode=s,this.setIndex=a,this.render=l,this.renderInstances=c,this.renderMultiDraw=h,this.renderMultiDrawInstances=d}function zx(n){let t={geometries:0,textures:0},e={frame:0,calls:0,triangles:0,points:0,lines:0};function i(r,o,a){switch(e.calls++,o){case n.TRIANGLES:e.triangles+=a*(r/3);break;case n.LINES:e.lines+=a*(r/2);break;case n.LINE_STRIP:e.lines+=a*(r-1);break;case n.LINE_LOOP:e.lines+=a*r;break;case n.POINTS:e.points+=a*r;break;default:console.error("THREE.WebGLInfo: Unknown draw mode:",o);break}}function s(){e.calls=0,e.triangles=0,e.points=0,e.lines=0}return{memory:t,render:e,programs:null,autoReset:!0,reset:s,update:i}}function kx(n,t,e){let i=new WeakMap,s=new ae;function r(o,a,l){let c=o.morphTargetInfluences,h=a.morphAttributes.position||a.morphAttributes.normal||a.morphAttributes.color,d=h!==void 0?h.length:0,p=i.get(a);if(p===void 0||p.count!==d){let G=function(){T.dispose(),i.delete(a),a.removeEventListener("dispose",G)};p!==void 0&&p.texture.dispose();let m=a.morphAttributes.position!==void 0,g=a.morphAttributes.normal!==void 0,_=a.morphAttributes.color!==void 0,u=a.morphAttributes.position||[],f=a.morphAttributes.normal||[],b=a.morphAttributes.color||[],S=0;m===!0&&(S=1),g===!0&&(S=2),_===!0&&(S=3);let E=a.attributes.position.count*S,C=1;E>t.maxTextureSize&&(C=Math.ceil(E/t.maxTextureSize),E=t.maxTextureSize);let A=new Float32Array(E*C*4*d),T=new Ro(A,E,C,d);T.type=Mn,T.needsUpdate=!0;let P=S*4;for(let x=0;x<d;x++){let y=u[x],V=f[x],U=b[x],H=E*C*4*x;for(let $=0;$<y.count;$++){let k=$*P;m===!0&&(s.fromBufferAttribute(y,$),A[H+k+0]=s.x,A[H+k+1]=s.y,A[H+k+2]=s.z,A[H+k+3]=0),g===!0&&(s.fromBufferAttribute(V,$),A[H+k+4]=s.x,A[H+k+5]=s.y,A[H+k+6]=s.z,A[H+k+7]=0),_===!0&&(s.fromBufferAttribute(U,$),A[H+k+8]=s.x,A[H+k+9]=s.y,A[H+k+10]=s.z,A[H+k+11]=U.itemSize===4?s.w:1)}}p={count:d,texture:T,size:new Zt(E,C)},i.set(a,p),a.addEventListener("dispose",G)}if(o.isInstancedMesh===!0&&o.morphTexture!==null)l.getUniforms().setValue(n,"morphTexture",o.morphTexture,e);else{let m=0;for(let _=0;_<c.length;_++)m+=c[_];let g=a.morphTargetsRelative?1:1-m;l.getUniforms().setValue(n,"morphTargetBaseInfluence",g),l.getUniforms().setValue(n,"morphTargetInfluences",c)}l.getUniforms().setValue(n,"morphTargetsTexture",p.texture,e),l.getUniforms().setValue(n,"morphTargetsTextureSize",p.size)}return{update:r}}function Hx(n,t,e,i){let s=new WeakMap;function r(l){let c=i.render.frame,h=l.geometry,d=t.get(l,h);if(s.get(d)!==c&&(t.update(d),s.set(d,c)),l.isInstancedMesh&&(l.hasEventListener("dispose",a)===!1&&l.addEventListener("dispose",a),s.get(l)!==c&&(e.update(l.instanceMatrix,n.ARRAY_BUFFER),l.instanceColor!==null&&e.update(l.instanceColor,n.ARRAY_BUFFER),s.set(l,c))),l.isSkinnedMesh){let p=l.skeleton;s.get(p)!==c&&(p.update(),s.set(p,c))}return d}function o(){s=new WeakMap}function a(l){let c=l.target;c.removeEventListener("dispose",a),e.remove(c.instanceMatrix),c.instanceColor!==null&&e.remove(c.instanceColor)}return{update:r,dispose:o}}var Bo=class extends Xe{constructor(t,e,i,s,r,o,a,l,c,h=rs){if(h!==rs&&h!==fs)throw new Error("DepthTexture format must be either THREE.DepthFormat or THREE.DepthStencilFormat");i===void 0&&h===rs&&(i=pi),i===void 0&&h===fs&&(i=us),super(null,s,r,o,a,l,h,i,c),this.isDepthTexture=!0,this.image={width:t,height:e},this.magFilter=a!==void 0?a:We,this.minFilter=l!==void 0?l:We,this.flipY=!1,this.generateMipmaps=!1,this.compareFunction=null}copy(t){return super.copy(t),this.compareFunction=t.compareFunction,this}toJSON(t){let e=super.toJSON(t);return this.compareFunction!==null&&(e.compareFunction=this.compareFunction),e}},jf=new Xe,uf=new Bo(1,1),td=new Ro,ed=new Yl,nd=new Uo,ff=[],df=[],pf=new Float32Array(16),mf=new Float32Array(9),gf=new Float32Array(4);function xs(n,t,e){let i=n[0];if(i<=0||i>0)return n;let s=t*e,r=ff[s];if(r===void 0&&(r=new Float32Array(s),ff[s]=r),t!==0){i.toArray(r,0);for(let o=1,a=0;o!==t;++o)a+=e,n[o].toArray(r,a)}return r}function fe(n,t){if(n.length!==t.length)return!1;for(let e=0,i=n.length;e<i;e++)if(n[e]!==t[e])return!1;return!0}function de(n,t){for(let e=0,i=t.length;e<i;e++)n[e]=t[e]}function $o(n,t){let e=df[t];e===void 0&&(e=new Int32Array(t),df[t]=e);for(let i=0;i!==t;++i)e[i]=n.allocateTextureUnit();return e}function Gx(n,t){let e=this.cache;e[0]!==t&&(n.uniform1f(this.addr,t),e[0]=t)}function Wx(n,t){let e=this.cache;if(t.x!==void 0)(e[0]!==t.x||e[1]!==t.y)&&(n.uniform2f(this.addr,t.x,t.y),e[0]=t.x,e[1]=t.y);else{if(fe(e,t))return;n.uniform2fv(this.addr,t),de(e,t)}}function Xx(n,t){let e=this.cache;if(t.x!==void 0)(e[0]!==t.x||e[1]!==t.y||e[2]!==t.z)&&(n.uniform3f(this.addr,t.x,t.y,t.z),e[0]=t.x,e[1]=t.y,e[2]=t.z);else if(t.r!==void 0)(e[0]!==t.r||e[1]!==t.g||e[2]!==t.b)&&(n.uniform3f(this.addr,t.r,t.g,t.b),e[0]=t.r,e[1]=t.g,e[2]=t.b);else{if(fe(e,t))return;n.uniform3fv(this.addr,t),de(e,t)}}function qx(n,t){let e=this.cache;if(t.x!==void 0)(e[0]!==t.x||e[1]!==t.y||e[2]!==t.z||e[3]!==t.w)&&(n.uniform4f(this.addr,t.x,t.y,t.z,t.w),e[0]=t.x,e[1]=t.y,e[2]=t.z,e[3]=t.w);else{if(fe(e,t))return;n.uniform4fv(this.addr,t),de(e,t)}}function Yx(n,t){let e=this.cache,i=t.elements;if(i===void 0){if(fe(e,t))return;n.uniformMatrix2fv(this.addr,!1,t),de(e,t)}else{if(fe(e,i))return;gf.set(i),n.uniformMatrix2fv(this.addr,!1,gf),de(e,i)}}function Kx(n,t){let e=this.cache,i=t.elements;if(i===void 0){if(fe(e,t))return;n.uniformMatrix3fv(this.addr,!1,t),de(e,t)}else{if(fe(e,i))return;mf.set(i),n.uniformMatrix3fv(this.addr,!1,mf),de(e,i)}}function $x(n,t){let e=this.cache,i=t.elements;if(i===void 0){if(fe(e,t))return;n.uniformMatrix4fv(this.addr,!1,t),de(e,t)}else{if(fe(e,i))return;pf.set(i),n.uniformMatrix4fv(this.addr,!1,pf),de(e,i)}}function Zx(n,t){let e=this.cache;e[0]!==t&&(n.uniform1i(this.addr,t),e[0]=t)}function Jx(n,t){let e=this.cache;if(t.x!==void 0)(e[0]!==t.x||e[1]!==t.y)&&(n.uniform2i(this.addr,t.x,t.y),e[0]=t.x,e[1]=t.y);else{if(fe(e,t))return;n.uniform2iv(this.addr,t),de(e,t)}}function Qx(n,t){let e=this.cache;if(t.x!==void 0)(e[0]!==t.x||e[1]!==t.y||e[2]!==t.z)&&(n.uniform3i(this.addr,t.x,t.y,t.z),e[0]=t.x,e[1]=t.y,e[2]=t.z);else{if(fe(e,t))return;n.uniform3iv(this.addr,t),de(e,t)}}function jx(n,t){let e=this.cache;if(t.x!==void 0)(e[0]!==t.x||e[1]!==t.y||e[2]!==t.z||e[3]!==t.w)&&(n.uniform4i(this.addr,t.x,t.y,t.z,t.w),e[0]=t.x,e[1]=t.y,e[2]=t.z,e[3]=t.w);else{if(fe(e,t))return;n.uniform4iv(this.addr,t),de(e,t)}}function t_(n,t){let e=this.cache;e[0]!==t&&(n.uniform1ui(this.addr,t),e[0]=t)}function e_(n,t){let e=this.cache;if(t.x!==void 0)(e[0]!==t.x||e[1]!==t.y)&&(n.uniform2ui(this.addr,t.x,t.y),e[0]=t.x,e[1]=t.y);else{if(fe(e,t))return;n.uniform2uiv(this.addr,t),de(e,t)}}function n_(n,t){let e=this.cache;if(t.x!==void 0)(e[0]!==t.x||e[1]!==t.y||e[2]!==t.z)&&(n.uniform3ui(this.addr,t.x,t.y,t.z),e[0]=t.x,e[1]=t.y,e[2]=t.z);else{if(fe(e,t))return;n.uniform3uiv(this.addr,t),de(e,t)}}function i_(n,t){let e=this.cache;if(t.x!==void 0)(e[0]!==t.x||e[1]!==t.y||e[2]!==t.z||e[3]!==t.w)&&(n.uniform4ui(this.addr,t.x,t.y,t.z,t.w),e[0]=t.x,e[1]=t.y,e[2]=t.z,e[3]=t.w);else{if(fe(e,t))return;n.uniform4uiv(this.addr,t),de(e,t)}}function s_(n,t,e){let i=this.cache,s=e.allocateTextureUnit();i[0]!==s&&(n.uniform1i(this.addr,s),i[0]=s);let r;this.type===n.SAMPLER_2D_SHADOW?(uf.compareFunction=Kf,r=uf):r=jf,e.setTexture2D(t||r,s)}function r_(n,t,e){let i=this.cache,s=e.allocateTextureUnit();i[0]!==s&&(n.uniform1i(this.addr,s),i[0]=s),e.setTexture3D(t||ed,s)}function o_(n,t,e){let i=this.cache,s=e.allocateTextureUnit();i[0]!==s&&(n.uniform1i(this.addr,s),i[0]=s),e.setTextureCube(t||nd,s)}function a_(n,t,e){let i=this.cache,s=e.allocateTextureUnit();i[0]!==s&&(n.uniform1i(this.addr,s),i[0]=s),e.setTexture2DArray(t||td,s)}function l_(n){switch(n){case 5126:return Gx;case 35664:return Wx;case 35665:return Xx;case 35666:return qx;case 35674:return Yx;case 35675:return Kx;case 35676:return $x;case 5124:case 35670:return Zx;case 35667:case 35671:return Jx;case 35668:case 35672:return Qx;case 35669:case 35673:return jx;case 5125:return t_;case 36294:return e_;case 36295:return n_;case 36296:return i_;case 35678:case 36198:case 36298:case 36306:case 35682:return s_;case 35679:case 36299:case 36307:return r_;case 35680:case 36300:case 36308:case 36293:return o_;case 36289:case 36303:case 36311:case 36292:return a_}}function c_(n,t){n.uniform1fv(this.addr,t)}function h_(n,t){let e=xs(t,this.size,2);n.uniform2fv(this.addr,e)}function u_(n,t){let e=xs(t,this.size,3);n.uniform3fv(this.addr,e)}function f_(n,t){let e=xs(t,this.size,4);n.uniform4fv(this.addr,e)}function d_(n,t){let e=xs(t,this.size,4);n.uniformMatrix2fv(this.addr,!1,e)}function p_(n,t){let e=xs(t,this.size,9);n.uniformMatrix3fv(this.addr,!1,e)}function m_(n,t){let e=xs(t,this.size,16);n.uniformMatrix4fv(this.addr,!1,e)}function g_(n,t){n.uniform1iv(this.addr,t)}function x_(n,t){n.uniform2iv(this.addr,t)}function __(n,t){n.uniform3iv(this.addr,t)}function v_(n,t){n.uniform4iv(this.addr,t)}function y_(n,t){n.uniform1uiv(this.addr,t)}function M_(n,t){n.uniform2uiv(this.addr,t)}function S_(n,t){n.uniform3uiv(this.addr,t)}function b_(n,t){n.uniform4uiv(this.addr,t)}function E_(n,t,e){let i=this.cache,s=t.length,r=$o(e,s);fe(i,r)||(n.uniform1iv(this.addr,r),de(i,r));for(let o=0;o!==s;++o)e.setTexture2D(t[o]||jf,r[o])}function w_(n,t,e){let i=this.cache,s=t.length,r=$o(e,s);fe(i,r)||(n.uniform1iv(this.addr,r),de(i,r));for(let o=0;o!==s;++o)e.setTexture3D(t[o]||ed,r[o])}function T_(n,t,e){let i=this.cache,s=t.length,r=$o(e,s);fe(i,r)||(n.uniform1iv(this.addr,r),de(i,r));for(let o=0;o!==s;++o)e.setTextureCube(t[o]||nd,r[o])}function A_(n,t,e){let i=this.cache,s=t.length,r=$o(e,s);fe(i,r)||(n.uniform1iv(this.addr,r),de(i,r));for(let o=0;o!==s;++o)e.setTexture2DArray(t[o]||td,r[o])}function C_(n){switch(n){case 5126:return c_;case 35664:return h_;case 35665:return u_;case 35666:return f_;case 35674:return d_;case 35675:return p_;case 35676:return m_;case 5124:case 35670:return g_;case 35667:case 35671:return x_;case 35668:case 35672:return __;case 35669:case 35673:return v_;case 5125:return y_;case 36294:return M_;case 36295:return S_;case 36296:return b_;case 35678:case 36198:case 36298:case 36306:case 35682:return E_;case 35679:case 36299:case 36307:return w_;case 35680:case 36300:case 36308:case 36293:return T_;case 36289:case 36303:case 36311:case 36292:return A_}}var Jl=class{constructor(t,e,i){this.id=t,this.addr=i,this.cache=[],this.type=e.type,this.setValue=l_(e.type)}},Ql=class{constructor(t,e,i){this.id=t,this.addr=i,this.cache=[],this.type=e.type,this.size=e.size,this.setValue=C_(e.type)}},jl=class{constructor(t){this.id=t,this.seq=[],this.map={}}setValue(t,e,i){let s=this.seq;for(let r=0,o=s.length;r!==o;++r){let a=s[r];a.setValue(t,e[a.id],i)}}},nl=/(\w+)(\])?(\[|\.)?/g;function xf(n,t){n.seq.push(t),n.map[t.id]=t}function R_(n,t,e){let i=n.name,s=i.length;for(nl.lastIndex=0;;){let r=nl.exec(i),o=nl.lastIndex,a=r[1],l=r[2]==="]",c=r[3];if(l&&(a=a|0),c===void 0||c==="["&&o+2===s){xf(e,c===void 0?new Jl(a,n,t):new Ql(a,n,t));break}else{let d=e.map[a];d===void 0&&(d=new jl(a),xf(e,d)),e=d}}}var as=class{constructor(t,e){this.seq=[],this.map={};let i=t.getProgramParameter(e,t.ACTIVE_UNIFORMS);for(let s=0;s<i;++s){let r=t.getActiveUniform(e,s),o=t.getUniformLocation(e,r.name);R_(r,o,this)}}setValue(t,e,i,s){let r=this.map[e];r!==void 0&&r.setValue(t,i,s)}setOptional(t,e,i){let s=e[i];s!==void 0&&this.setValue(t,i,s)}static upload(t,e,i,s){for(let r=0,o=e.length;r!==o;++r){let a=e[r],l=i[a.id];l.needsUpdate!==!1&&a.setValue(t,l.value,s)}}static seqWithValue(t,e){let i=[];for(let s=0,r=t.length;s!==r;++s){let o=t[s];o.id in e&&i.push(o)}return i}};function _f(n,t,e){let i=n.createShader(t);return n.shaderSource(i,e),n.compileShader(i),i}var P_=37297,I_=0;function L_(n,t){let e=n.split(`
`),i=[],s=Math.max(t-6,0),r=Math.min(t+6,e.length);for(let o=s;o<r;o++){let a=o+1;i.push(`${a===t?">":" "} ${a}: ${e[o]}`)}return i.join(`
`)}function D_(n){let t=Yt.getPrimaries(Yt.workingColorSpace),e=Yt.getPrimaries(n),i;switch(t===e?i="":t===wo&&e===Eo?i="LinearDisplayP3ToLinearSRGB":t===Eo&&e===wo&&(i="LinearSRGBToLinearDisplayP3"),n){case $n:case Ko:return[i,"LinearTransferOETF"];case an:case Ac:return[i,"sRGBTransferOETF"];default:return console.warn("THREE.WebGLProgram: Unsupported color space:",n),[i,"LinearTransferOETF"]}}function vf(n,t,e){let i=n.getShaderParameter(t,n.COMPILE_STATUS),s=n.getShaderInfoLog(t).trim();if(i&&s==="")return"";let r=/ERROR: 0:(\d+)/.exec(s);if(r){let o=parseInt(r[1]);return e.toUpperCase()+`

`+s+`

`+L_(n.getShaderSource(t),o)}else return s}function U_(n,t){let e=D_(t);return`vec4 ${n}( vec4 value ) { return ${e[0]}( ${e[1]}( value ) ); }`}function N_(n,t){let e;switch(t){case sm:e="Linear";break;case rm:e="Reinhard";break;case om:e="Cineon";break;case am:e="ACESFilmic";break;case cm:e="AgX";break;case hm:e="Neutral";break;case lm:e="Custom";break;default:console.warn("THREE.WebGLProgram: Unsupported toneMapping:",t),e="Linear"}return"vec3 "+n+"( vec3 color ) { return "+e+"ToneMapping( color ); }"}var lo=new F;function F_(){Yt.getLuminanceCoefficients(lo);let n=lo.x.toFixed(4),t=lo.y.toFixed(4),e=lo.z.toFixed(4);return["float luminance( const in vec3 rgb ) {",`	const vec3 weights = vec3( ${n}, ${t}, ${e} );`,"	return dot( weights, rgb );","}"].join(`
`)}function O_(n){return[n.extensionClipCullDistance?"#extension GL_ANGLE_clip_cull_distance : require":"",n.extensionMultiDraw?"#extension GL_ANGLE_multi_draw : require":""].filter(Bs).join(`
`)}function B_(n){let t=[];for(let e in n){let i=n[e];i!==!1&&t.push("#define "+e+" "+i)}return t.join(`
`)}function V_(n,t){let e={},i=n.getProgramParameter(t,n.ACTIVE_ATTRIBUTES);for(let s=0;s<i;s++){let r=n.getActiveAttrib(t,s),o=r.name,a=1;r.type===n.FLOAT_MAT2&&(a=2),r.type===n.FLOAT_MAT3&&(a=3),r.type===n.FLOAT_MAT4&&(a=4),e[o]={type:r.type,location:n.getAttribLocation(t,o),locationSize:a}}return e}function Bs(n){return n!==""}function yf(n,t){let e=t.numSpotLightShadows+t.numSpotLightMaps-t.numSpotLightShadowsWithMaps;return n.replace(/NUM_DIR_LIGHTS/g,t.numDirLights).replace(/NUM_SPOT_LIGHTS/g,t.numSpotLights).replace(/NUM_SPOT_LIGHT_MAPS/g,t.numSpotLightMaps).replace(/NUM_SPOT_LIGHT_COORDS/g,e).replace(/NUM_RECT_AREA_LIGHTS/g,t.numRectAreaLights).replace(/NUM_POINT_LIGHTS/g,t.numPointLights).replace(/NUM_HEMI_LIGHTS/g,t.numHemiLights).replace(/NUM_DIR_LIGHT_SHADOWS/g,t.numDirLightShadows).replace(/NUM_SPOT_LIGHT_SHADOWS_WITH_MAPS/g,t.numSpotLightShadowsWithMaps).replace(/NUM_SPOT_LIGHT_SHADOWS/g,t.numSpotLightShadows).replace(/NUM_POINT_LIGHT_SHADOWS/g,t.numPointLightShadows)}function Mf(n,t){return n.replace(/NUM_CLIPPING_PLANES/g,t.numClippingPlanes).replace(/UNION_CLIPPING_PLANES/g,t.numClippingPlanes-t.numClipIntersection)}var z_=/^[ \t]*#include +<([\w\d./]+)>/gm;function tc(n){return n.replace(z_,H_)}var k_=new Map;function H_(n,t){let e=Nt[t];if(e===void 0){let i=k_.get(t);if(i!==void 0)e=Nt[i],console.warn('THREE.WebGLRenderer: Shader chunk "%s" has been deprecated. Use "%s" instead.',t,i);else throw new Error("Can not resolve #include <"+t+">")}return tc(e)}var G_=/#pragma unroll_loop_start\s+for\s*\(\s*int\s+i\s*=\s*(\d+)\s*;\s*i\s*<\s*(\d+)\s*;\s*i\s*\+\+\s*\)\s*{([\s\S]+?)}\s+#pragma unroll_loop_end/g;function Sf(n){return n.replace(G_,W_)}function W_(n,t,e,i){let s="";for(let r=parseInt(t);r<parseInt(e);r++)s+=i.replace(/\[\s*i\s*\]/g,"[ "+r+" ]").replace(/UNROLLED_LOOP_INDEX/g,r);return s}function bf(n){let t=`precision ${n.precision} float;
	precision ${n.precision} int;
	precision ${n.precision} sampler2D;
	precision ${n.precision} samplerCube;
	precision ${n.precision} sampler3D;
	precision ${n.precision} sampler2DArray;
	precision ${n.precision} sampler2DShadow;
	precision ${n.precision} samplerCubeShadow;
	precision ${n.precision} sampler2DArrayShadow;
	precision ${n.precision} isampler2D;
	precision ${n.precision} isampler3D;
	precision ${n.precision} isamplerCube;
	precision ${n.precision} isampler2DArray;
	precision ${n.precision} usampler2D;
	precision ${n.precision} usampler3D;
	precision ${n.precision} usamplerCube;
	precision ${n.precision} usampler2DArray;
	`;return n.precision==="highp"?t+=`
#define HIGH_PRECISION`:n.precision==="mediump"?t+=`
#define MEDIUM_PRECISION`:n.precision==="lowp"&&(t+=`
#define LOW_PRECISION`),t}function X_(n){let t="SHADOWMAP_TYPE_BASIC";return n.shadowMapType===Nf?t="SHADOWMAP_TYPE_PCF":n.shadowMapType===Op?t="SHADOWMAP_TYPE_PCF_SOFT":n.shadowMapType===vn&&(t="SHADOWMAP_TYPE_VSM"),t}function q_(n){let t="ENVMAP_TYPE_CUBE";if(n.envMap)switch(n.envMapMode){case cs:case hs:t="ENVMAP_TYPE_CUBE";break;case Yo:t="ENVMAP_TYPE_CUBE_UV";break}return t}function Y_(n){let t="ENVMAP_MODE_REFLECTION";if(n.envMap)switch(n.envMapMode){case hs:t="ENVMAP_MODE_REFRACTION";break}return t}function K_(n){let t="ENVMAP_BLENDING_NONE";if(n.envMap)switch(n.combine){case Ff:t="ENVMAP_BLENDING_MULTIPLY";break;case nm:t="ENVMAP_BLENDING_MIX";break;case im:t="ENVMAP_BLENDING_ADD";break}return t}function $_(n){let t=n.envMapCubeUVHeight;if(t===null)return null;let e=Math.log2(t)-2,i=1/t;return{texelWidth:1/(3*Math.max(Math.pow(2,e),7*16)),texelHeight:i,maxMip:e}}function Z_(n,t,e,i){let s=n.getContext(),r=e.defines,o=e.vertexShader,a=e.fragmentShader,l=X_(e),c=q_(e),h=Y_(e),d=K_(e),p=$_(e),m=O_(e),g=B_(r),_=s.createProgram(),u,f,b=e.glslVersion?"#version "+e.glslVersion+`
`:"";e.isRawShaderMaterial?(u=["#define SHADER_TYPE "+e.shaderType,"#define SHADER_NAME "+e.shaderName,g].filter(Bs).join(`
`),u.length>0&&(u+=`
`),f=["#define SHADER_TYPE "+e.shaderType,"#define SHADER_NAME "+e.shaderName,g].filter(Bs).join(`
`),f.length>0&&(f+=`
`)):(u=[bf(e),"#define SHADER_TYPE "+e.shaderType,"#define SHADER_NAME "+e.shaderName,g,e.extensionClipCullDistance?"#define USE_CLIP_DISTANCE":"",e.batching?"#define USE_BATCHING":"",e.batchingColor?"#define USE_BATCHING_COLOR":"",e.instancing?"#define USE_INSTANCING":"",e.instancingColor?"#define USE_INSTANCING_COLOR":"",e.instancingMorph?"#define USE_INSTANCING_MORPH":"",e.useFog&&e.fog?"#define USE_FOG":"",e.useFog&&e.fogExp2?"#define FOG_EXP2":"",e.map?"#define USE_MAP":"",e.envMap?"#define USE_ENVMAP":"",e.envMap?"#define "+h:"",e.lightMap?"#define USE_LIGHTMAP":"",e.aoMap?"#define USE_AOMAP":"",e.bumpMap?"#define USE_BUMPMAP":"",e.normalMap?"#define USE_NORMALMAP":"",e.normalMapObjectSpace?"#define USE_NORMALMAP_OBJECTSPACE":"",e.normalMapTangentSpace?"#define USE_NORMALMAP_TANGENTSPACE":"",e.displacementMap?"#define USE_DISPLACEMENTMAP":"",e.emissiveMap?"#define USE_EMISSIVEMAP":"",e.anisotropy?"#define USE_ANISOTROPY":"",e.anisotropyMap?"#define USE_ANISOTROPYMAP":"",e.clearcoatMap?"#define USE_CLEARCOATMAP":"",e.clearcoatRoughnessMap?"#define USE_CLEARCOAT_ROUGHNESSMAP":"",e.clearcoatNormalMap?"#define USE_CLEARCOAT_NORMALMAP":"",e.iridescenceMap?"#define USE_IRIDESCENCEMAP":"",e.iridescenceThicknessMap?"#define USE_IRIDESCENCE_THICKNESSMAP":"",e.specularMap?"#define USE_SPECULARMAP":"",e.specularColorMap?"#define USE_SPECULAR_COLORMAP":"",e.specularIntensityMap?"#define USE_SPECULAR_INTENSITYMAP":"",e.roughnessMap?"#define USE_ROUGHNESSMAP":"",e.metalnessMap?"#define USE_METALNESSMAP":"",e.alphaMap?"#define USE_ALPHAMAP":"",e.alphaHash?"#define USE_ALPHAHASH":"",e.transmission?"#define USE_TRANSMISSION":"",e.transmissionMap?"#define USE_TRANSMISSIONMAP":"",e.thicknessMap?"#define USE_THICKNESSMAP":"",e.sheenColorMap?"#define USE_SHEEN_COLORMAP":"",e.sheenRoughnessMap?"#define USE_SHEEN_ROUGHNESSMAP":"",e.mapUv?"#define MAP_UV "+e.mapUv:"",e.alphaMapUv?"#define ALPHAMAP_UV "+e.alphaMapUv:"",e.lightMapUv?"#define LIGHTMAP_UV "+e.lightMapUv:"",e.aoMapUv?"#define AOMAP_UV "+e.aoMapUv:"",e.emissiveMapUv?"#define EMISSIVEMAP_UV "+e.emissiveMapUv:"",e.bumpMapUv?"#define BUMPMAP_UV "+e.bumpMapUv:"",e.normalMapUv?"#define NORMALMAP_UV "+e.normalMapUv:"",e.displacementMapUv?"#define DISPLACEMENTMAP_UV "+e.displacementMapUv:"",e.metalnessMapUv?"#define METALNESSMAP_UV "+e.metalnessMapUv:"",e.roughnessMapUv?"#define ROUGHNESSMAP_UV "+e.roughnessMapUv:"",e.anisotropyMapUv?"#define ANISOTROPYMAP_UV "+e.anisotropyMapUv:"",e.clearcoatMapUv?"#define CLEARCOATMAP_UV "+e.clearcoatMapUv:"",e.clearcoatNormalMapUv?"#define CLEARCOAT_NORMALMAP_UV "+e.clearcoatNormalMapUv:"",e.clearcoatRoughnessMapUv?"#define CLEARCOAT_ROUGHNESSMAP_UV "+e.clearcoatRoughnessMapUv:"",e.iridescenceMapUv?"#define IRIDESCENCEMAP_UV "+e.iridescenceMapUv:"",e.iridescenceThicknessMapUv?"#define IRIDESCENCE_THICKNESSMAP_UV "+e.iridescenceThicknessMapUv:"",e.sheenColorMapUv?"#define SHEEN_COLORMAP_UV "+e.sheenColorMapUv:"",e.sheenRoughnessMapUv?"#define SHEEN_ROUGHNESSMAP_UV "+e.sheenRoughnessMapUv:"",e.specularMapUv?"#define SPECULARMAP_UV "+e.specularMapUv:"",e.specularColorMapUv?"#define SPECULAR_COLORMAP_UV "+e.specularColorMapUv:"",e.specularIntensityMapUv?"#define SPECULAR_INTENSITYMAP_UV "+e.specularIntensityMapUv:"",e.transmissionMapUv?"#define TRANSMISSIONMAP_UV "+e.transmissionMapUv:"",e.thicknessMapUv?"#define THICKNESSMAP_UV "+e.thicknessMapUv:"",e.vertexTangents&&e.flatShading===!1?"#define USE_TANGENT":"",e.vertexColors?"#define USE_COLOR":"",e.vertexAlphas?"#define USE_COLOR_ALPHA":"",e.vertexUv1s?"#define USE_UV1":"",e.vertexUv2s?"#define USE_UV2":"",e.vertexUv3s?"#define USE_UV3":"",e.pointsUvs?"#define USE_POINTS_UV":"",e.flatShading?"#define FLAT_SHADED":"",e.skinning?"#define USE_SKINNING":"",e.morphTargets?"#define USE_MORPHTARGETS":"",e.morphNormals&&e.flatShading===!1?"#define USE_MORPHNORMALS":"",e.morphColors?"#define USE_MORPHCOLORS":"",e.morphTargetsCount>0?"#define MORPHTARGETS_TEXTURE_STRIDE "+e.morphTextureStride:"",e.morphTargetsCount>0?"#define MORPHTARGETS_COUNT "+e.morphTargetsCount:"",e.doubleSided?"#define DOUBLE_SIDED":"",e.flipSided?"#define FLIP_SIDED":"",e.shadowMapEnabled?"#define USE_SHADOWMAP":"",e.shadowMapEnabled?"#define "+l:"",e.sizeAttenuation?"#define USE_SIZEATTENUATION":"",e.numLightProbes>0?"#define USE_LIGHT_PROBES":"",e.logarithmicDepthBuffer?"#define USE_LOGDEPTHBUF":"",e.reverseDepthBuffer?"#define USE_REVERSEDEPTHBUF":"","uniform mat4 modelMatrix;","uniform mat4 modelViewMatrix;","uniform mat4 projectionMatrix;","uniform mat4 viewMatrix;","uniform mat3 normalMatrix;","uniform vec3 cameraPosition;","uniform bool isOrthographic;","#ifdef USE_INSTANCING","	attribute mat4 instanceMatrix;","#endif","#ifdef USE_INSTANCING_COLOR","	attribute vec3 instanceColor;","#endif","#ifdef USE_INSTANCING_MORPH","	uniform sampler2D morphTexture;","#endif","attribute vec3 position;","attribute vec3 normal;","attribute vec2 uv;","#ifdef USE_UV1","	attribute vec2 uv1;","#endif","#ifdef USE_UV2","	attribute vec2 uv2;","#endif","#ifdef USE_UV3","	attribute vec2 uv3;","#endif","#ifdef USE_TANGENT","	attribute vec4 tangent;","#endif","#if defined( USE_COLOR_ALPHA )","	attribute vec4 color;","#elif defined( USE_COLOR )","	attribute vec3 color;","#endif","#ifdef USE_SKINNING","	attribute vec4 skinIndex;","	attribute vec4 skinWeight;","#endif",`
`].filter(Bs).join(`
`),f=[bf(e),"#define SHADER_TYPE "+e.shaderType,"#define SHADER_NAME "+e.shaderName,g,e.useFog&&e.fog?"#define USE_FOG":"",e.useFog&&e.fogExp2?"#define FOG_EXP2":"",e.alphaToCoverage?"#define ALPHA_TO_COVERAGE":"",e.map?"#define USE_MAP":"",e.matcap?"#define USE_MATCAP":"",e.envMap?"#define USE_ENVMAP":"",e.envMap?"#define "+c:"",e.envMap?"#define "+h:"",e.envMap?"#define "+d:"",p?"#define CUBEUV_TEXEL_WIDTH "+p.texelWidth:"",p?"#define CUBEUV_TEXEL_HEIGHT "+p.texelHeight:"",p?"#define CUBEUV_MAX_MIP "+p.maxMip+".0":"",e.lightMap?"#define USE_LIGHTMAP":"",e.aoMap?"#define USE_AOMAP":"",e.bumpMap?"#define USE_BUMPMAP":"",e.normalMap?"#define USE_NORMALMAP":"",e.normalMapObjectSpace?"#define USE_NORMALMAP_OBJECTSPACE":"",e.normalMapTangentSpace?"#define USE_NORMALMAP_TANGENTSPACE":"",e.emissiveMap?"#define USE_EMISSIVEMAP":"",e.anisotropy?"#define USE_ANISOTROPY":"",e.anisotropyMap?"#define USE_ANISOTROPYMAP":"",e.clearcoat?"#define USE_CLEARCOAT":"",e.clearcoatMap?"#define USE_CLEARCOATMAP":"",e.clearcoatRoughnessMap?"#define USE_CLEARCOAT_ROUGHNESSMAP":"",e.clearcoatNormalMap?"#define USE_CLEARCOAT_NORMALMAP":"",e.dispersion?"#define USE_DISPERSION":"",e.iridescence?"#define USE_IRIDESCENCE":"",e.iridescenceMap?"#define USE_IRIDESCENCEMAP":"",e.iridescenceThicknessMap?"#define USE_IRIDESCENCE_THICKNESSMAP":"",e.specularMap?"#define USE_SPECULARMAP":"",e.specularColorMap?"#define USE_SPECULAR_COLORMAP":"",e.specularIntensityMap?"#define USE_SPECULAR_INTENSITYMAP":"",e.roughnessMap?"#define USE_ROUGHNESSMAP":"",e.metalnessMap?"#define USE_METALNESSMAP":"",e.alphaMap?"#define USE_ALPHAMAP":"",e.alphaTest?"#define USE_ALPHATEST":"",e.alphaHash?"#define USE_ALPHAHASH":"",e.sheen?"#define USE_SHEEN":"",e.sheenColorMap?"#define USE_SHEEN_COLORMAP":"",e.sheenRoughnessMap?"#define USE_SHEEN_ROUGHNESSMAP":"",e.transmission?"#define USE_TRANSMISSION":"",e.transmissionMap?"#define USE_TRANSMISSIONMAP":"",e.thicknessMap?"#define USE_THICKNESSMAP":"",e.vertexTangents&&e.flatShading===!1?"#define USE_TANGENT":"",e.vertexColors||e.instancingColor||e.batchingColor?"#define USE_COLOR":"",e.vertexAlphas?"#define USE_COLOR_ALPHA":"",e.vertexUv1s?"#define USE_UV1":"",e.vertexUv2s?"#define USE_UV2":"",e.vertexUv3s?"#define USE_UV3":"",e.pointsUvs?"#define USE_POINTS_UV":"",e.gradientMap?"#define USE_GRADIENTMAP":"",e.flatShading?"#define FLAT_SHADED":"",e.doubleSided?"#define DOUBLE_SIDED":"",e.flipSided?"#define FLIP_SIDED":"",e.shadowMapEnabled?"#define USE_SHADOWMAP":"",e.shadowMapEnabled?"#define "+l:"",e.premultipliedAlpha?"#define PREMULTIPLIED_ALPHA":"",e.numLightProbes>0?"#define USE_LIGHT_PROBES":"",e.decodeVideoTexture?"#define DECODE_VIDEO_TEXTURE":"",e.logarithmicDepthBuffer?"#define USE_LOGDEPTHBUF":"",e.reverseDepthBuffer?"#define USE_REVERSEDEPTHBUF":"","uniform mat4 viewMatrix;","uniform vec3 cameraPosition;","uniform bool isOrthographic;",e.toneMapping!==Wn?"#define TONE_MAPPING":"",e.toneMapping!==Wn?Nt.tonemapping_pars_fragment:"",e.toneMapping!==Wn?N_("toneMapping",e.toneMapping):"",e.dithering?"#define DITHERING":"",e.opaque?"#define OPAQUE":"",Nt.colorspace_pars_fragment,U_("linearToOutputTexel",e.outputColorSpace),F_(),e.useDepthPacking?"#define DEPTH_PACKING "+e.depthPacking:"",`
`].filter(Bs).join(`
`)),o=tc(o),o=yf(o,e),o=Mf(o,e),a=tc(a),a=yf(a,e),a=Mf(a,e),o=Sf(o),a=Sf(a),e.isRawShaderMaterial!==!0&&(b=`#version 300 es
`,u=[m,"#define attribute in","#define varying out","#define texture2D texture"].join(`
`)+`
`+u,f=["#define varying in",e.glslVersion===zu?"":"layout(location = 0) out highp vec4 pc_fragColor;",e.glslVersion===zu?"":"#define gl_FragColor pc_fragColor","#define gl_FragDepthEXT gl_FragDepth","#define texture2D texture","#define textureCube texture","#define texture2DProj textureProj","#define texture2DLodEXT textureLod","#define texture2DProjLodEXT textureProjLod","#define textureCubeLodEXT textureLod","#define texture2DGradEXT textureGrad","#define texture2DProjGradEXT textureProjGrad","#define textureCubeGradEXT textureGrad"].join(`
`)+`
`+f);let S=b+u+o,E=b+f+a,C=_f(s,s.VERTEX_SHADER,S),A=_f(s,s.FRAGMENT_SHADER,E);s.attachShader(_,C),s.attachShader(_,A),e.index0AttributeName!==void 0?s.bindAttribLocation(_,0,e.index0AttributeName):e.morphTargets===!0&&s.bindAttribLocation(_,0,"position"),s.linkProgram(_);function T(y){if(n.debug.checkShaderErrors){let V=s.getProgramInfoLog(_).trim(),U=s.getShaderInfoLog(C).trim(),H=s.getShaderInfoLog(A).trim(),$=!0,k=!0;if(s.getProgramParameter(_,s.LINK_STATUS)===!1)if($=!1,typeof n.debug.onShaderError=="function")n.debug.onShaderError(s,_,C,A);else{let Z=vf(s,C,"vertex"),z=vf(s,A,"fragment");console.error("THREE.WebGLProgram: Shader Error "+s.getError()+" - VALIDATE_STATUS "+s.getProgramParameter(_,s.VALIDATE_STATUS)+`

Material Name: `+y.name+`
Material Type: `+y.type+`

Program Info Log: `+V+`
`+Z+`
`+z)}else V!==""?console.warn("THREE.WebGLProgram: Program Info Log:",V):(U===""||H==="")&&(k=!1);k&&(y.diagnostics={runnable:$,programLog:V,vertexShader:{log:U,prefix:u},fragmentShader:{log:H,prefix:f}})}s.deleteShader(C),s.deleteShader(A),P=new as(s,_),G=V_(s,_)}let P;this.getUniforms=function(){return P===void 0&&T(this),P};let G;this.getAttributes=function(){return G===void 0&&T(this),G};let x=e.rendererExtensionParallelShaderCompile===!1;return this.isReady=function(){return x===!1&&(x=s.getProgramParameter(_,P_)),x},this.destroy=function(){i.releaseStatesOfProgram(this),s.deleteProgram(_),this.program=void 0},this.type=e.shaderType,this.name=e.shaderName,this.id=I_++,this.cacheKey=t,this.usedTimes=1,this.program=_,this.vertexShader=C,this.fragmentShader=A,this}var J_=0,ec=class{constructor(){this.shaderCache=new Map,this.materialCache=new Map}update(t){let e=t.vertexShader,i=t.fragmentShader,s=this._getShaderStage(e),r=this._getShaderStage(i),o=this._getShaderCacheForMaterial(t);return o.has(s)===!1&&(o.add(s),s.usedTimes++),o.has(r)===!1&&(o.add(r),r.usedTimes++),this}remove(t){let e=this.materialCache.get(t);for(let i of e)i.usedTimes--,i.usedTimes===0&&this.shaderCache.delete(i.code);return this.materialCache.delete(t),this}getVertexShaderID(t){return this._getShaderStage(t.vertexShader).id}getFragmentShaderID(t){return this._getShaderStage(t.fragmentShader).id}dispose(){this.shaderCache.clear(),this.materialCache.clear()}_getShaderCacheForMaterial(t){let e=this.materialCache,i=e.get(t);return i===void 0&&(i=new Set,e.set(t,i)),i}_getShaderStage(t){let e=this.shaderCache,i=e.get(t);return i===void 0&&(i=new nc(t),e.set(t,i)),i}},nc=class{constructor(t){this.id=J_++,this.code=t,this.usedTimes=0}};function Q_(n,t,e,i,s,r,o){let a=new Po,l=new ec,c=new Set,h=[],d=s.logarithmicDepthBuffer,p=s.reverseDepthBuffer,m=s.vertexTextures,g=s.precision,_={MeshDepthMaterial:"depth",MeshDistanceMaterial:"distanceRGBA",MeshNormalMaterial:"normal",MeshBasicMaterial:"basic",MeshLambertMaterial:"lambert",MeshPhongMaterial:"phong",MeshToonMaterial:"toon",MeshStandardMaterial:"physical",MeshPhysicalMaterial:"physical",MeshMatcapMaterial:"matcap",LineBasicMaterial:"basic",LineDashedMaterial:"dashed",PointsMaterial:"points",ShadowMaterial:"shadow",SpriteMaterial:"sprite"};function u(x){return c.add(x),x===0?"uv":`uv${x}`}function f(x,y,V,U,H){let $=U.fog,k=H.geometry,Z=x.isMeshStandardMaterial?U.environment:null,z=(x.isMeshStandardMaterial?e:t).get(x.envMap||Z),it=z&&z.mapping===Yo?z.image.height:null,ot=_[x.type];x.precision!==null&&(g=s.getMaxPrecision(x.precision),g!==x.precision&&console.warn("THREE.WebGLProgram.getParameters:",x.precision,"not supported, using",g,"instead."));let pt=k.morphAttributes.position||k.morphAttributes.normal||k.morphAttributes.color,Bt=pt!==void 0?pt.length:0,Vt=0;k.morphAttributes.position!==void 0&&(Vt=1),k.morphAttributes.normal!==void 0&&(Vt=2),k.morphAttributes.color!==void 0&&(Vt=3);let X,J,ht,rt;if(ot){let Ee=ln[ot];X=Ee.vertexShader,J=Ee.fragmentShader}else X=x.vertexShader,J=x.fragmentShader,l.update(x),ht=l.getVertexShaderID(x),rt=l.getFragmentShaderID(x);let bt=n.getRenderTarget(),et=H.isInstancedMesh===!0,at=H.isBatchedMesh===!0,Et=!!x.map,It=!!x.matcap,R=!!z,ie=!!x.aoMap,Ut=!!x.lightMap,Ot=!!x.bumpMap,wt=!!x.normalMap,$t=!!x.displacementMap,Tt=!!x.emissiveMap,w=!!x.metalnessMap,v=!!x.roughnessMap,N=x.anisotropy>0,Y=x.clearcoat>0,Q=x.dispersion>0,q=x.iridescence>0,vt=x.sheen>0,st=x.transmission>0,dt=N&&!!x.anisotropyMap,Ht=Y&&!!x.clearcoatMap,j=Y&&!!x.clearcoatNormalMap,mt=Y&&!!x.clearcoatRoughnessMap,Rt=q&&!!x.iridescenceMap,Pt=q&&!!x.iridescenceThicknessMap,gt=vt&&!!x.sheenColorMap,zt=vt&&!!x.sheenRoughnessMap,Lt=!!x.specularMap,Jt=!!x.specularColorMap,I=!!x.specularIntensityMap,ut=st&&!!x.transmissionMap,W=st&&!!x.thicknessMap,K=!!x.gradientMap,lt=!!x.alphaMap,ft=x.alphaTest>0,kt=!!x.alphaHash,ce=!!x.extensions,be=Wn;x.toneMapped&&(bt===null||bt.isXRRenderTarget===!0)&&(be=n.toneMapping);let Wt={shaderID:ot,shaderType:x.type,shaderName:x.name,vertexShader:X,fragmentShader:J,defines:x.defines,customVertexShaderID:ht,customFragmentShaderID:rt,isRawShaderMaterial:x.isRawShaderMaterial===!0,glslVersion:x.glslVersion,precision:g,batching:at,batchingColor:at&&H._colorsTexture!==null,instancing:et,instancingColor:et&&H.instanceColor!==null,instancingMorph:et&&H.morphTexture!==null,supportsVertexTextures:m,outputColorSpace:bt===null?n.outputColorSpace:bt.isXRRenderTarget===!0?bt.texture.colorSpace:$n,alphaToCoverage:!!x.alphaToCoverage,map:Et,matcap:It,envMap:R,envMapMode:R&&z.mapping,envMapCubeUVHeight:it,aoMap:ie,lightMap:Ut,bumpMap:Ot,normalMap:wt,displacementMap:m&&$t,emissiveMap:Tt,normalMapObjectSpace:wt&&x.normalMapType===mm,normalMapTangentSpace:wt&&x.normalMapType===pm,metalnessMap:w,roughnessMap:v,anisotropy:N,anisotropyMap:dt,clearcoat:Y,clearcoatMap:Ht,clearcoatNormalMap:j,clearcoatRoughnessMap:mt,dispersion:Q,iridescence:q,iridescenceMap:Rt,iridescenceThicknessMap:Pt,sheen:vt,sheenColorMap:gt,sheenRoughnessMap:zt,specularMap:Lt,specularColorMap:Jt,specularIntensityMap:I,transmission:st,transmissionMap:ut,thicknessMap:W,gradientMap:K,opaque:x.transparent===!1&&x.blending===ss&&x.alphaToCoverage===!1,alphaMap:lt,alphaTest:ft,alphaHash:kt,combine:x.combine,mapUv:Et&&u(x.map.channel),aoMapUv:ie&&u(x.aoMap.channel),lightMapUv:Ut&&u(x.lightMap.channel),bumpMapUv:Ot&&u(x.bumpMap.channel),normalMapUv:wt&&u(x.normalMap.channel),displacementMapUv:$t&&u(x.displacementMap.channel),emissiveMapUv:Tt&&u(x.emissiveMap.channel),metalnessMapUv:w&&u(x.metalnessMap.channel),roughnessMapUv:v&&u(x.roughnessMap.channel),anisotropyMapUv:dt&&u(x.anisotropyMap.channel),clearcoatMapUv:Ht&&u(x.clearcoatMap.channel),clearcoatNormalMapUv:j&&u(x.clearcoatNormalMap.channel),clearcoatRoughnessMapUv:mt&&u(x.clearcoatRoughnessMap.channel),iridescenceMapUv:Rt&&u(x.iridescenceMap.channel),iridescenceThicknessMapUv:Pt&&u(x.iridescenceThicknessMap.channel),sheenColorMapUv:gt&&u(x.sheenColorMap.channel),sheenRoughnessMapUv:zt&&u(x.sheenRoughnessMap.channel),specularMapUv:Lt&&u(x.specularMap.channel),specularColorMapUv:Jt&&u(x.specularColorMap.channel),specularIntensityMapUv:I&&u(x.specularIntensityMap.channel),transmissionMapUv:ut&&u(x.transmissionMap.channel),thicknessMapUv:W&&u(x.thicknessMap.channel),alphaMapUv:lt&&u(x.alphaMap.channel),vertexTangents:!!k.attributes.tangent&&(wt||N),vertexColors:x.vertexColors,vertexAlphas:x.vertexColors===!0&&!!k.attributes.color&&k.attributes.color.itemSize===4,pointsUvs:H.isPoints===!0&&!!k.attributes.uv&&(Et||lt),fog:!!$,useFog:x.fog===!0,fogExp2:!!$&&$.isFogExp2,flatShading:x.flatShading===!0,sizeAttenuation:x.sizeAttenuation===!0,logarithmicDepthBuffer:d,reverseDepthBuffer:p,skinning:H.isSkinnedMesh===!0,morphTargets:k.morphAttributes.position!==void 0,morphNormals:k.morphAttributes.normal!==void 0,morphColors:k.morphAttributes.color!==void 0,morphTargetsCount:Bt,morphTextureStride:Vt,numDirLights:y.directional.length,numPointLights:y.point.length,numSpotLights:y.spot.length,numSpotLightMaps:y.spotLightMap.length,numRectAreaLights:y.rectArea.length,numHemiLights:y.hemi.length,numDirLightShadows:y.directionalShadowMap.length,numPointLightShadows:y.pointShadowMap.length,numSpotLightShadows:y.spotShadowMap.length,numSpotLightShadowsWithMaps:y.numSpotLightShadowsWithMaps,numLightProbes:y.numLightProbes,numClippingPlanes:o.numPlanes,numClipIntersection:o.numIntersection,dithering:x.dithering,shadowMapEnabled:n.shadowMap.enabled&&V.length>0,shadowMapType:n.shadowMap.type,toneMapping:be,decodeVideoTexture:Et&&x.map.isVideoTexture===!0&&Yt.getTransfer(x.map.colorSpace)===te,premultipliedAlpha:x.premultipliedAlpha,doubleSided:x.side===Je,flipSided:x.side===Re,useDepthPacking:x.depthPacking>=0,depthPacking:x.depthPacking||0,index0AttributeName:x.index0AttributeName,extensionClipCullDistance:ce&&x.extensions.clipCullDistance===!0&&i.has("WEBGL_clip_cull_distance"),extensionMultiDraw:(ce&&x.extensions.multiDraw===!0||at)&&i.has("WEBGL_multi_draw"),rendererExtensionParallelShaderCompile:i.has("KHR_parallel_shader_compile"),customProgramCacheKey:x.customProgramCacheKey()};return Wt.vertexUv1s=c.has(1),Wt.vertexUv2s=c.has(2),Wt.vertexUv3s=c.has(3),c.clear(),Wt}function b(x){let y=[];if(x.shaderID?y.push(x.shaderID):(y.push(x.customVertexShaderID),y.push(x.customFragmentShaderID)),x.defines!==void 0)for(let V in x.defines)y.push(V),y.push(x.defines[V]);return x.isRawShaderMaterial===!1&&(S(y,x),E(y,x),y.push(n.outputColorSpace)),y.push(x.customProgramCacheKey),y.join()}function S(x,y){x.push(y.precision),x.push(y.outputColorSpace),x.push(y.envMapMode),x.push(y.envMapCubeUVHeight),x.push(y.mapUv),x.push(y.alphaMapUv),x.push(y.lightMapUv),x.push(y.aoMapUv),x.push(y.bumpMapUv),x.push(y.normalMapUv),x.push(y.displacementMapUv),x.push(y.emissiveMapUv),x.push(y.metalnessMapUv),x.push(y.roughnessMapUv),x.push(y.anisotropyMapUv),x.push(y.clearcoatMapUv),x.push(y.clearcoatNormalMapUv),x.push(y.clearcoatRoughnessMapUv),x.push(y.iridescenceMapUv),x.push(y.iridescenceThicknessMapUv),x.push(y.sheenColorMapUv),x.push(y.sheenRoughnessMapUv),x.push(y.specularMapUv),x.push(y.specularColorMapUv),x.push(y.specularIntensityMapUv),x.push(y.transmissionMapUv),x.push(y.thicknessMapUv),x.push(y.combine),x.push(y.fogExp2),x.push(y.sizeAttenuation),x.push(y.morphTargetsCount),x.push(y.morphAttributeCount),x.push(y.numDirLights),x.push(y.numPointLights),x.push(y.numSpotLights),x.push(y.numSpotLightMaps),x.push(y.numHemiLights),x.push(y.numRectAreaLights),x.push(y.numDirLightShadows),x.push(y.numPointLightShadows),x.push(y.numSpotLightShadows),x.push(y.numSpotLightShadowsWithMaps),x.push(y.numLightProbes),x.push(y.shadowMapType),x.push(y.toneMapping),x.push(y.numClippingPlanes),x.push(y.numClipIntersection),x.push(y.depthPacking)}function E(x,y){a.disableAll(),y.supportsVertexTextures&&a.enable(0),y.instancing&&a.enable(1),y.instancingColor&&a.enable(2),y.instancingMorph&&a.enable(3),y.matcap&&a.enable(4),y.envMap&&a.enable(5),y.normalMapObjectSpace&&a.enable(6),y.normalMapTangentSpace&&a.enable(7),y.clearcoat&&a.enable(8),y.iridescence&&a.enable(9),y.alphaTest&&a.enable(10),y.vertexColors&&a.enable(11),y.vertexAlphas&&a.enable(12),y.vertexUv1s&&a.enable(13),y.vertexUv2s&&a.enable(14),y.vertexUv3s&&a.enable(15),y.vertexTangents&&a.enable(16),y.anisotropy&&a.enable(17),y.alphaHash&&a.enable(18),y.batching&&a.enable(19),y.dispersion&&a.enable(20),y.batchingColor&&a.enable(21),x.push(a.mask),a.disableAll(),y.fog&&a.enable(0),y.useFog&&a.enable(1),y.flatShading&&a.enable(2),y.logarithmicDepthBuffer&&a.enable(3),y.reverseDepthBuffer&&a.enable(4),y.skinning&&a.enable(5),y.morphTargets&&a.enable(6),y.morphNormals&&a.enable(7),y.morphColors&&a.enable(8),y.premultipliedAlpha&&a.enable(9),y.shadowMapEnabled&&a.enable(10),y.doubleSided&&a.enable(11),y.flipSided&&a.enable(12),y.useDepthPacking&&a.enable(13),y.dithering&&a.enable(14),y.transmission&&a.enable(15),y.sheen&&a.enable(16),y.opaque&&a.enable(17),y.pointsUvs&&a.enable(18),y.decodeVideoTexture&&a.enable(19),y.alphaToCoverage&&a.enable(20),x.push(a.mask)}function C(x){let y=_[x.type],V;if(y){let U=ln[y];V=Hm.clone(U.uniforms)}else V=x.uniforms;return V}function A(x,y){let V;for(let U=0,H=h.length;U<H;U++){let $=h[U];if($.cacheKey===y){V=$,++V.usedTimes;break}}return V===void 0&&(V=new Z_(n,y,x,r),h.push(V)),V}function T(x){if(--x.usedTimes===0){let y=h.indexOf(x);h[y]=h[h.length-1],h.pop(),x.destroy()}}function P(x){l.remove(x)}function G(){l.dispose()}return{getParameters:f,getProgramCacheKey:b,getUniforms:C,acquireProgram:A,releaseProgram:T,releaseShaderCache:P,programs:h,dispose:G}}function j_(){let n=new WeakMap;function t(o){return n.has(o)}function e(o){let a=n.get(o);return a===void 0&&(a={},n.set(o,a)),a}function i(o){n.delete(o)}function s(o,a,l){n.get(o)[a]=l}function r(){n=new WeakMap}return{has:t,get:e,remove:i,update:s,dispose:r}}function tv(n,t){return n.groupOrder!==t.groupOrder?n.groupOrder-t.groupOrder:n.renderOrder!==t.renderOrder?n.renderOrder-t.renderOrder:n.material.id!==t.material.id?n.material.id-t.material.id:n.z!==t.z?n.z-t.z:n.id-t.id}function Ef(n,t){return n.groupOrder!==t.groupOrder?n.groupOrder-t.groupOrder:n.renderOrder!==t.renderOrder?n.renderOrder-t.renderOrder:n.z!==t.z?t.z-n.z:n.id-t.id}function wf(){let n=[],t=0,e=[],i=[],s=[];function r(){t=0,e.length=0,i.length=0,s.length=0}function o(d,p,m,g,_,u){let f=n[t];return f===void 0?(f={id:d.id,object:d,geometry:p,material:m,groupOrder:g,renderOrder:d.renderOrder,z:_,group:u},n[t]=f):(f.id=d.id,f.object=d,f.geometry=p,f.material=m,f.groupOrder=g,f.renderOrder=d.renderOrder,f.z=_,f.group=u),t++,f}function a(d,p,m,g,_,u){let f=o(d,p,m,g,_,u);m.transmission>0?i.push(f):m.transparent===!0?s.push(f):e.push(f)}function l(d,p,m,g,_,u){let f=o(d,p,m,g,_,u);m.transmission>0?i.unshift(f):m.transparent===!0?s.unshift(f):e.unshift(f)}function c(d,p){e.length>1&&e.sort(d||tv),i.length>1&&i.sort(p||Ef),s.length>1&&s.sort(p||Ef)}function h(){for(let d=t,p=n.length;d<p;d++){let m=n[d];if(m.id===null)break;m.id=null,m.object=null,m.geometry=null,m.material=null,m.group=null}}return{opaque:e,transmissive:i,transparent:s,init:r,push:a,unshift:l,finish:h,sort:c}}function ev(){let n=new WeakMap;function t(i,s){let r=n.get(i),o;return r===void 0?(o=new wf,n.set(i,[o])):s>=r.length?(o=new wf,r.push(o)):o=r[s],o}function e(){n=new WeakMap}return{get:t,dispose:e}}function nv(){let n={};return{get:function(t){if(n[t.id]!==void 0)return n[t.id];let e;switch(t.type){case"DirectionalLight":e={direction:new F,color:new Gt};break;case"SpotLight":e={position:new F,direction:new F,color:new Gt,distance:0,coneCos:0,penumbraCos:0,decay:0};break;case"PointLight":e={position:new F,color:new Gt,distance:0,decay:0};break;case"HemisphereLight":e={direction:new F,skyColor:new Gt,groundColor:new Gt};break;case"RectAreaLight":e={color:new Gt,position:new F,halfWidth:new F,halfHeight:new F};break}return n[t.id]=e,e}}}function iv(){let n={};return{get:function(t){if(n[t.id]!==void 0)return n[t.id];let e;switch(t.type){case"DirectionalLight":e={shadowIntensity:1,shadowBias:0,shadowNormalBias:0,shadowRadius:1,shadowMapSize:new Zt};break;case"SpotLight":e={shadowIntensity:1,shadowBias:0,shadowNormalBias:0,shadowRadius:1,shadowMapSize:new Zt};break;case"PointLight":e={shadowIntensity:1,shadowBias:0,shadowNormalBias:0,shadowRadius:1,shadowMapSize:new Zt,shadowCameraNear:1,shadowCameraFar:1e3};break}return n[t.id]=e,e}}}var sv=0;function rv(n,t){return(t.castShadow?2:0)-(n.castShadow?2:0)+(t.map?1:0)-(n.map?1:0)}function ov(n){let t=new nv,e=iv(),i={version:0,hash:{directionalLength:-1,pointLength:-1,spotLength:-1,rectAreaLength:-1,hemiLength:-1,numDirectionalShadows:-1,numPointShadows:-1,numSpotShadows:-1,numSpotMaps:-1,numLightProbes:-1},ambient:[0,0,0],probe:[],directional:[],directionalShadow:[],directionalShadowMap:[],directionalShadowMatrix:[],spot:[],spotLightMap:[],spotShadow:[],spotShadowMap:[],spotLightMatrix:[],rectArea:[],rectAreaLTC1:null,rectAreaLTC2:null,point:[],pointShadow:[],pointShadowMap:[],pointShadowMatrix:[],hemi:[],numSpotLightShadowsWithMaps:0,numLightProbes:0};for(let c=0;c<9;c++)i.probe.push(new F);let s=new F,r=new le,o=new le;function a(c){let h=0,d=0,p=0;for(let G=0;G<9;G++)i.probe[G].set(0,0,0);let m=0,g=0,_=0,u=0,f=0,b=0,S=0,E=0,C=0,A=0,T=0;c.sort(rv);for(let G=0,x=c.length;G<x;G++){let y=c[G],V=y.color,U=y.intensity,H=y.distance,$=y.shadow&&y.shadow.map?y.shadow.map.texture:null;if(y.isAmbientLight)h+=V.r*U,d+=V.g*U,p+=V.b*U;else if(y.isLightProbe){for(let k=0;k<9;k++)i.probe[k].addScaledVector(y.sh.coefficients[k],U);T++}else if(y.isDirectionalLight){let k=t.get(y);if(k.color.copy(y.color).multiplyScalar(y.intensity),y.castShadow){let Z=y.shadow,z=e.get(y);z.shadowIntensity=Z.intensity,z.shadowBias=Z.bias,z.shadowNormalBias=Z.normalBias,z.shadowRadius=Z.radius,z.shadowMapSize=Z.mapSize,i.directionalShadow[m]=z,i.directionalShadowMap[m]=$,i.directionalShadowMatrix[m]=y.shadow.matrix,b++}i.directional[m]=k,m++}else if(y.isSpotLight){let k=t.get(y);k.position.setFromMatrixPosition(y.matrixWorld),k.color.copy(V).multiplyScalar(U),k.distance=H,k.coneCos=Math.cos(y.angle),k.penumbraCos=Math.cos(y.angle*(1-y.penumbra)),k.decay=y.decay,i.spot[_]=k;let Z=y.shadow;if(y.map&&(i.spotLightMap[C]=y.map,C++,Z.updateMatrices(y),y.castShadow&&A++),i.spotLightMatrix[_]=Z.matrix,y.castShadow){let z=e.get(y);z.shadowIntensity=Z.intensity,z.shadowBias=Z.bias,z.shadowNormalBias=Z.normalBias,z.shadowRadius=Z.radius,z.shadowMapSize=Z.mapSize,i.spotShadow[_]=z,i.spotShadowMap[_]=$,E++}_++}else if(y.isRectAreaLight){let k=t.get(y);k.color.copy(V).multiplyScalar(U),k.halfWidth.set(y.width*.5,0,0),k.halfHeight.set(0,y.height*.5,0),i.rectArea[u]=k,u++}else if(y.isPointLight){let k=t.get(y);if(k.color.copy(y.color).multiplyScalar(y.intensity),k.distance=y.distance,k.decay=y.decay,y.castShadow){let Z=y.shadow,z=e.get(y);z.shadowIntensity=Z.intensity,z.shadowBias=Z.bias,z.shadowNormalBias=Z.normalBias,z.shadowRadius=Z.radius,z.shadowMapSize=Z.mapSize,z.shadowCameraNear=Z.camera.near,z.shadowCameraFar=Z.camera.far,i.pointShadow[g]=z,i.pointShadowMap[g]=$,i.pointShadowMatrix[g]=y.shadow.matrix,S++}i.point[g]=k,g++}else if(y.isHemisphereLight){let k=t.get(y);k.skyColor.copy(y.color).multiplyScalar(U),k.groundColor.copy(y.groundColor).multiplyScalar(U),i.hemi[f]=k,f++}}u>0&&(n.has("OES_texture_float_linear")===!0?(i.rectAreaLTC1=nt.LTC_FLOAT_1,i.rectAreaLTC2=nt.LTC_FLOAT_2):(i.rectAreaLTC1=nt.LTC_HALF_1,i.rectAreaLTC2=nt.LTC_HALF_2)),i.ambient[0]=h,i.ambient[1]=d,i.ambient[2]=p;let P=i.hash;(P.directionalLength!==m||P.pointLength!==g||P.spotLength!==_||P.rectAreaLength!==u||P.hemiLength!==f||P.numDirectionalShadows!==b||P.numPointShadows!==S||P.numSpotShadows!==E||P.numSpotMaps!==C||P.numLightProbes!==T)&&(i.directional.length=m,i.spot.length=_,i.rectArea.length=u,i.point.length=g,i.hemi.length=f,i.directionalShadow.length=b,i.directionalShadowMap.length=b,i.pointShadow.length=S,i.pointShadowMap.length=S,i.spotShadow.length=E,i.spotShadowMap.length=E,i.directionalShadowMatrix.length=b,i.pointShadowMatrix.length=S,i.spotLightMatrix.length=E+C-A,i.spotLightMap.length=C,i.numSpotLightShadowsWithMaps=A,i.numLightProbes=T,P.directionalLength=m,P.pointLength=g,P.spotLength=_,P.rectAreaLength=u,P.hemiLength=f,P.numDirectionalShadows=b,P.numPointShadows=S,P.numSpotShadows=E,P.numSpotMaps=C,P.numLightProbes=T,i.version=sv++)}function l(c,h){let d=0,p=0,m=0,g=0,_=0,u=h.matrixWorldInverse;for(let f=0,b=c.length;f<b;f++){let S=c[f];if(S.isDirectionalLight){let E=i.directional[d];E.direction.setFromMatrixPosition(S.matrixWorld),s.setFromMatrixPosition(S.target.matrixWorld),E.direction.sub(s),E.direction.transformDirection(u),d++}else if(S.isSpotLight){let E=i.spot[m];E.position.setFromMatrixPosition(S.matrixWorld),E.position.applyMatrix4(u),E.direction.setFromMatrixPosition(S.matrixWorld),s.setFromMatrixPosition(S.target.matrixWorld),E.direction.sub(s),E.direction.transformDirection(u),m++}else if(S.isRectAreaLight){let E=i.rectArea[g];E.position.setFromMatrixPosition(S.matrixWorld),E.position.applyMatrix4(u),o.identity(),r.copy(S.matrixWorld),r.premultiply(u),o.extractRotation(r),E.halfWidth.set(S.width*.5,0,0),E.halfHeight.set(0,S.height*.5,0),E.halfWidth.applyMatrix4(o),E.halfHeight.applyMatrix4(o),g++}else if(S.isPointLight){let E=i.point[p];E.position.setFromMatrixPosition(S.matrixWorld),E.position.applyMatrix4(u),p++}else if(S.isHemisphereLight){let E=i.hemi[_];E.direction.setFromMatrixPosition(S.matrixWorld),E.direction.transformDirection(u),_++}}}return{setup:a,setupView:l,state:i}}function Tf(n){let t=new ov(n),e=[],i=[];function s(h){c.camera=h,e.length=0,i.length=0}function r(h){e.push(h)}function o(h){i.push(h)}function a(){t.setup(e)}function l(h){t.setupView(e,h)}let c={lightsArray:e,shadowsArray:i,camera:null,lights:t,transmissionRenderTarget:{}};return{init:s,state:c,setupLights:a,setupLightsView:l,pushLight:r,pushShadow:o}}function av(n){let t=new WeakMap;function e(s,r=0){let o=t.get(s),a;return o===void 0?(a=new Tf(n),t.set(s,[a])):r>=o.length?(a=new Tf(n),o.push(a)):a=o[r],a}function i(){t=new WeakMap}return{get:e,dispose:i}}var ic=class extends Kn{constructor(t){super(),this.isMeshDepthMaterial=!0,this.type="MeshDepthMaterial",this.depthPacking=fm,this.map=null,this.alphaMap=null,this.displacementMap=null,this.displacementScale=1,this.displacementBias=0,this.wireframe=!1,this.wireframeLinewidth=1,this.setValues(t)}copy(t){return super.copy(t),this.depthPacking=t.depthPacking,this.map=t.map,this.alphaMap=t.alphaMap,this.displacementMap=t.displacementMap,this.displacementScale=t.displacementScale,this.displacementBias=t.displacementBias,this.wireframe=t.wireframe,this.wireframeLinewidth=t.wireframeLinewidth,this}},sc=class extends Kn{constructor(t){super(),this.isMeshDistanceMaterial=!0,this.type="MeshDistanceMaterial",this.map=null,this.alphaMap=null,this.displacementMap=null,this.displacementScale=1,this.displacementBias=0,this.setValues(t)}copy(t){return super.copy(t),this.map=t.map,this.alphaMap=t.alphaMap,this.displacementMap=t.displacementMap,this.displacementScale=t.displacementScale,this.displacementBias=t.displacementBias,this}},lv=`void main() {
	gl_Position = vec4( position, 1.0 );
}`,cv=`uniform sampler2D shadow_pass;
uniform vec2 resolution;
uniform float radius;
#include <packing>
void main() {
	const float samples = float( VSM_SAMPLES );
	float mean = 0.0;
	float squared_mean = 0.0;
	float uvStride = samples <= 1.0 ? 0.0 : 2.0 / ( samples - 1.0 );
	float uvStart = samples <= 1.0 ? 0.0 : - 1.0;
	for ( float i = 0.0; i < samples; i ++ ) {
		float uvOffset = uvStart + i * uvStride;
		#ifdef HORIZONTAL_PASS
			vec2 distribution = unpackRGBATo2Half( texture2D( shadow_pass, ( gl_FragCoord.xy + vec2( uvOffset, 0.0 ) * radius ) / resolution ) );
			mean += distribution.x;
			squared_mean += distribution.y * distribution.y + distribution.x * distribution.x;
		#else
			float depth = unpackRGBAToDepth( texture2D( shadow_pass, ( gl_FragCoord.xy + vec2( 0.0, uvOffset ) * radius ) / resolution ) );
			mean += depth;
			squared_mean += depth * depth;
		#endif
	}
	mean = mean / samples;
	squared_mean = squared_mean / samples;
	float std_dev = sqrt( squared_mean - mean * mean );
	gl_FragColor = pack2HalfToRGBA( vec2( mean, std_dev ) );
}`;function hv(n,t,e){let i=new No,s=new Zt,r=new Zt,o=new ae,a=new ic({depthPacking:dm}),l=new sc,c={},h=e.maxTextureSize,d={[Xn]:Re,[Re]:Xn,[Je]:Je},p=new cn({defines:{VSM_SAMPLES:8},uniforms:{shadow_pass:{value:null},resolution:{value:new Zt},radius:{value:4}},vertexShader:lv,fragmentShader:cv}),m=p.clone();m.defines.HORIZONTAL_PASS=1;let g=new Be;g.setAttribute("position",new Se(new Float32Array([-1,-1,.5,3,-1,.5,-1,3,.5]),3));let _=new Fe(g,p),u=this;this.enabled=!1,this.autoUpdate=!0,this.needsUpdate=!1,this.type=Nf;let f=this.type;this.render=function(A,T,P){if(u.enabled===!1||u.autoUpdate===!1&&u.needsUpdate===!1||A.length===0)return;let G=n.getRenderTarget(),x=n.getActiveCubeFace(),y=n.getActiveMipmapLevel(),V=n.state;V.setBlending(Gn),V.buffers.color.setClear(1,1,1,1),V.buffers.depth.setTest(!0),V.setScissorTest(!1);let U=f!==vn&&this.type===vn,H=f===vn&&this.type!==vn;for(let $=0,k=A.length;$<k;$++){let Z=A[$],z=Z.shadow;if(z===void 0){console.warn("THREE.WebGLShadowMap:",Z,"has no shadow.");continue}if(z.autoUpdate===!1&&z.needsUpdate===!1)continue;s.copy(z.mapSize);let it=z.getFrameExtents();if(s.multiply(it),r.copy(z.mapSize),(s.x>h||s.y>h)&&(s.x>h&&(r.x=Math.floor(h/it.x),s.x=r.x*it.x,z.mapSize.x=r.x),s.y>h&&(r.y=Math.floor(h/it.y),s.y=r.y*it.y,z.mapSize.y=r.y)),z.map===null||U===!0||H===!0){let pt=this.type!==vn?{minFilter:We,magFilter:We}:{};z.map!==null&&z.map.dispose(),z.map=new En(s.x,s.y,pt),z.map.texture.name=Z.name+".shadowMap",z.camera.updateProjectionMatrix()}n.setRenderTarget(z.map),n.clear();let ot=z.getViewportCount();for(let pt=0;pt<ot;pt++){let Bt=z.getViewport(pt);o.set(r.x*Bt.x,r.y*Bt.y,r.x*Bt.z,r.y*Bt.w),V.viewport(o),z.updateMatrices(Z,pt),i=z.getFrustum(),E(T,P,z.camera,Z,this.type)}z.isPointLightShadow!==!0&&this.type===vn&&b(z,P),z.needsUpdate=!1}f=this.type,u.needsUpdate=!1,n.setRenderTarget(G,x,y)};function b(A,T){let P=t.update(_);p.defines.VSM_SAMPLES!==A.blurSamples&&(p.defines.VSM_SAMPLES=A.blurSamples,m.defines.VSM_SAMPLES=A.blurSamples,p.needsUpdate=!0,m.needsUpdate=!0),A.mapPass===null&&(A.mapPass=new En(s.x,s.y)),p.uniforms.shadow_pass.value=A.map.texture,p.uniforms.resolution.value=A.mapSize,p.uniforms.radius.value=A.radius,n.setRenderTarget(A.mapPass),n.clear(),n.renderBufferDirect(T,null,P,p,_,null),m.uniforms.shadow_pass.value=A.mapPass.texture,m.uniforms.resolution.value=A.mapSize,m.uniforms.radius.value=A.radius,n.setRenderTarget(A.map),n.clear(),n.renderBufferDirect(T,null,P,m,_,null)}function S(A,T,P,G){let x=null,y=P.isPointLight===!0?A.customDistanceMaterial:A.customDepthMaterial;if(y!==void 0)x=y;else if(x=P.isPointLight===!0?l:a,n.localClippingEnabled&&T.clipShadows===!0&&Array.isArray(T.clippingPlanes)&&T.clippingPlanes.length!==0||T.displacementMap&&T.displacementScale!==0||T.alphaMap&&T.alphaTest>0||T.map&&T.alphaTest>0){let V=x.uuid,U=T.uuid,H=c[V];H===void 0&&(H={},c[V]=H);let $=H[U];$===void 0&&($=x.clone(),H[U]=$,T.addEventListener("dispose",C)),x=$}if(x.visible=T.visible,x.wireframe=T.wireframe,G===vn?x.side=T.shadowSide!==null?T.shadowSide:T.side:x.side=T.shadowSide!==null?T.shadowSide:d[T.side],x.alphaMap=T.alphaMap,x.alphaTest=T.alphaTest,x.map=T.map,x.clipShadows=T.clipShadows,x.clippingPlanes=T.clippingPlanes,x.clipIntersection=T.clipIntersection,x.displacementMap=T.displacementMap,x.displacementScale=T.displacementScale,x.displacementBias=T.displacementBias,x.wireframeLinewidth=T.wireframeLinewidth,x.linewidth=T.linewidth,P.isPointLight===!0&&x.isMeshDistanceMaterial===!0){let V=n.properties.get(x);V.light=P}return x}function E(A,T,P,G,x){if(A.visible===!1)return;if(A.layers.test(T.layers)&&(A.isMesh||A.isLine||A.isPoints)&&(A.castShadow||A.receiveShadow&&x===vn)&&(!A.frustumCulled||i.intersectsObject(A))){A.modelViewMatrix.multiplyMatrices(P.matrixWorldInverse,A.matrixWorld);let U=t.update(A),H=A.material;if(Array.isArray(H)){let $=U.groups;for(let k=0,Z=$.length;k<Z;k++){let z=$[k],it=H[z.materialIndex];if(it&&it.visible){let ot=S(A,it,G,x);A.onBeforeShadow(n,A,T,P,U,ot,z),n.renderBufferDirect(P,null,U,ot,A,z),A.onAfterShadow(n,A,T,P,U,ot,z)}}}else if(H.visible){let $=S(A,H,G,x);A.onBeforeShadow(n,A,T,P,U,$,null),n.renderBufferDirect(P,null,U,$,A,null),A.onAfterShadow(n,A,T,P,U,$,null)}}let V=A.children;for(let U=0,H=V.length;U<H;U++)E(V[U],T,P,G,x)}function C(A){A.target.removeEventListener("dispose",C);for(let P in c){let G=c[P],x=A.target.uuid;x in G&&(G[x].dispose(),delete G[x])}}}var uv={[ol]:al,[ll]:ul,[cl]:fl,[ls]:hl,[al]:ol,[ul]:ll,[fl]:cl,[hl]:ls};function fv(n){function t(){let I=!1,ut=new ae,W=null,K=new ae(0,0,0,0);return{setMask:function(lt){W!==lt&&!I&&(n.colorMask(lt,lt,lt,lt),W=lt)},setLocked:function(lt){I=lt},setClear:function(lt,ft,kt,ce,be){be===!0&&(lt*=ce,ft*=ce,kt*=ce),ut.set(lt,ft,kt,ce),K.equals(ut)===!1&&(n.clearColor(lt,ft,kt,ce),K.copy(ut))},reset:function(){I=!1,W=null,K.set(-1,0,0,0)}}}function e(){let I=!1,ut=!1,W=null,K=null,lt=null;return{setReversed:function(ft){ut=ft},setTest:function(ft){ft?ht(n.DEPTH_TEST):rt(n.DEPTH_TEST)},setMask:function(ft){W!==ft&&!I&&(n.depthMask(ft),W=ft)},setFunc:function(ft){if(ut&&(ft=uv[ft]),K!==ft){switch(ft){case ol:n.depthFunc(n.NEVER);break;case al:n.depthFunc(n.ALWAYS);break;case ll:n.depthFunc(n.LESS);break;case ls:n.depthFunc(n.LEQUAL);break;case cl:n.depthFunc(n.EQUAL);break;case hl:n.depthFunc(n.GEQUAL);break;case ul:n.depthFunc(n.GREATER);break;case fl:n.depthFunc(n.NOTEQUAL);break;default:n.depthFunc(n.LEQUAL)}K=ft}},setLocked:function(ft){I=ft},setClear:function(ft){lt!==ft&&(n.clearDepth(ft),lt=ft)},reset:function(){I=!1,W=null,K=null,lt=null}}}function i(){let I=!1,ut=null,W=null,K=null,lt=null,ft=null,kt=null,ce=null,be=null;return{setTest:function(Wt){I||(Wt?ht(n.STENCIL_TEST):rt(n.STENCIL_TEST))},setMask:function(Wt){ut!==Wt&&!I&&(n.stencilMask(Wt),ut=Wt)},setFunc:function(Wt,Ee,hn){(W!==Wt||K!==Ee||lt!==hn)&&(n.stencilFunc(Wt,Ee,hn),W=Wt,K=Ee,lt=hn)},setOp:function(Wt,Ee,hn){(ft!==Wt||kt!==Ee||ce!==hn)&&(n.stencilOp(Wt,Ee,hn),ft=Wt,kt=Ee,ce=hn)},setLocked:function(Wt){I=Wt},setClear:function(Wt){be!==Wt&&(n.clearStencil(Wt),be=Wt)},reset:function(){I=!1,ut=null,W=null,K=null,lt=null,ft=null,kt=null,ce=null,be=null}}}let s=new t,r=new e,o=new i,a=new WeakMap,l=new WeakMap,c={},h={},d=new WeakMap,p=[],m=null,g=!1,_=null,u=null,f=null,b=null,S=null,E=null,C=null,A=new Gt(0,0,0),T=0,P=!1,G=null,x=null,y=null,V=null,U=null,H=n.getParameter(n.MAX_COMBINED_TEXTURE_IMAGE_UNITS),$=!1,k=0,Z=n.getParameter(n.VERSION);Z.indexOf("WebGL")!==-1?(k=parseFloat(/^WebGL (\d)/.exec(Z)[1]),$=k>=1):Z.indexOf("OpenGL ES")!==-1&&(k=parseFloat(/^OpenGL ES (\d)/.exec(Z)[1]),$=k>=2);let z=null,it={},ot=n.getParameter(n.SCISSOR_BOX),pt=n.getParameter(n.VIEWPORT),Bt=new ae().fromArray(ot),Vt=new ae().fromArray(pt);function X(I,ut,W,K){let lt=new Uint8Array(4),ft=n.createTexture();n.bindTexture(I,ft),n.texParameteri(I,n.TEXTURE_MIN_FILTER,n.NEAREST),n.texParameteri(I,n.TEXTURE_MAG_FILTER,n.NEAREST);for(let kt=0;kt<W;kt++)I===n.TEXTURE_3D||I===n.TEXTURE_2D_ARRAY?n.texImage3D(ut,0,n.RGBA,1,1,K,0,n.RGBA,n.UNSIGNED_BYTE,lt):n.texImage2D(ut+kt,0,n.RGBA,1,1,0,n.RGBA,n.UNSIGNED_BYTE,lt);return ft}let J={};J[n.TEXTURE_2D]=X(n.TEXTURE_2D,n.TEXTURE_2D,1),J[n.TEXTURE_CUBE_MAP]=X(n.TEXTURE_CUBE_MAP,n.TEXTURE_CUBE_MAP_POSITIVE_X,6),J[n.TEXTURE_2D_ARRAY]=X(n.TEXTURE_2D_ARRAY,n.TEXTURE_2D_ARRAY,1,1),J[n.TEXTURE_3D]=X(n.TEXTURE_3D,n.TEXTURE_3D,1,1),s.setClear(0,0,0,1),r.setClear(1),o.setClear(0),ht(n.DEPTH_TEST),r.setFunc(ls),Ut(!1),Ot(Lu),ht(n.CULL_FACE),R(Gn);function ht(I){c[I]!==!0&&(n.enable(I),c[I]=!0)}function rt(I){c[I]!==!1&&(n.disable(I),c[I]=!1)}function bt(I,ut){return h[I]!==ut?(n.bindFramebuffer(I,ut),h[I]=ut,I===n.DRAW_FRAMEBUFFER&&(h[n.FRAMEBUFFER]=ut),I===n.FRAMEBUFFER&&(h[n.DRAW_FRAMEBUFFER]=ut),!0):!1}function et(I,ut){let W=p,K=!1;if(I){W=d.get(ut),W===void 0&&(W=[],d.set(ut,W));let lt=I.textures;if(W.length!==lt.length||W[0]!==n.COLOR_ATTACHMENT0){for(let ft=0,kt=lt.length;ft<kt;ft++)W[ft]=n.COLOR_ATTACHMENT0+ft;W.length=lt.length,K=!0}}else W[0]!==n.BACK&&(W[0]=n.BACK,K=!0);K&&n.drawBuffers(W)}function at(I){return m!==I?(n.useProgram(I),m=I,!0):!1}let Et={[ci]:n.FUNC_ADD,[Vp]:n.FUNC_SUBTRACT,[zp]:n.FUNC_REVERSE_SUBTRACT};Et[kp]=n.MIN,Et[Hp]=n.MAX;let It={[Gp]:n.ZERO,[Wp]:n.ONE,[Xp]:n.SRC_COLOR,[sl]:n.SRC_ALPHA,[Jp]:n.SRC_ALPHA_SATURATE,[$p]:n.DST_COLOR,[Yp]:n.DST_ALPHA,[qp]:n.ONE_MINUS_SRC_COLOR,[rl]:n.ONE_MINUS_SRC_ALPHA,[Zp]:n.ONE_MINUS_DST_COLOR,[Kp]:n.ONE_MINUS_DST_ALPHA,[Qp]:n.CONSTANT_COLOR,[jp]:n.ONE_MINUS_CONSTANT_COLOR,[tm]:n.CONSTANT_ALPHA,[em]:n.ONE_MINUS_CONSTANT_ALPHA};function R(I,ut,W,K,lt,ft,kt,ce,be,Wt){if(I===Gn){g===!0&&(rt(n.BLEND),g=!1);return}if(g===!1&&(ht(n.BLEND),g=!0),I!==Bp){if(I!==_||Wt!==P){if((u!==ci||S!==ci)&&(n.blendEquation(n.FUNC_ADD),u=ci,S=ci),Wt)switch(I){case ss:n.blendFuncSeparate(n.ONE,n.ONE_MINUS_SRC_ALPHA,n.ONE,n.ONE_MINUS_SRC_ALPHA);break;case Mo:n.blendFunc(n.ONE,n.ONE);break;case Du:n.blendFuncSeparate(n.ZERO,n.ONE_MINUS_SRC_COLOR,n.ZERO,n.ONE);break;case Uu:n.blendFuncSeparate(n.ZERO,n.SRC_COLOR,n.ZERO,n.SRC_ALPHA);break;default:console.error("THREE.WebGLState: Invalid blending: ",I);break}else switch(I){case ss:n.blendFuncSeparate(n.SRC_ALPHA,n.ONE_MINUS_SRC_ALPHA,n.ONE,n.ONE_MINUS_SRC_ALPHA);break;case Mo:n.blendFunc(n.SRC_ALPHA,n.ONE);break;case Du:n.blendFuncSeparate(n.ZERO,n.ONE_MINUS_SRC_COLOR,n.ZERO,n.ONE);break;case Uu:n.blendFunc(n.ZERO,n.SRC_COLOR);break;default:console.error("THREE.WebGLState: Invalid blending: ",I);break}f=null,b=null,E=null,C=null,A.set(0,0,0),T=0,_=I,P=Wt}return}lt=lt||ut,ft=ft||W,kt=kt||K,(ut!==u||lt!==S)&&(n.blendEquationSeparate(Et[ut],Et[lt]),u=ut,S=lt),(W!==f||K!==b||ft!==E||kt!==C)&&(n.blendFuncSeparate(It[W],It[K],It[ft],It[kt]),f=W,b=K,E=ft,C=kt),(ce.equals(A)===!1||be!==T)&&(n.blendColor(ce.r,ce.g,ce.b,be),A.copy(ce),T=be),_=I,P=!1}function ie(I,ut){I.side===Je?rt(n.CULL_FACE):ht(n.CULL_FACE);let W=I.side===Re;ut&&(W=!W),Ut(W),I.blending===ss&&I.transparent===!1?R(Gn):R(I.blending,I.blendEquation,I.blendSrc,I.blendDst,I.blendEquationAlpha,I.blendSrcAlpha,I.blendDstAlpha,I.blendColor,I.blendAlpha,I.premultipliedAlpha),r.setFunc(I.depthFunc),r.setTest(I.depthTest),r.setMask(I.depthWrite),s.setMask(I.colorWrite);let K=I.stencilWrite;o.setTest(K),K&&(o.setMask(I.stencilWriteMask),o.setFunc(I.stencilFunc,I.stencilRef,I.stencilFuncMask),o.setOp(I.stencilFail,I.stencilZFail,I.stencilZPass)),$t(I.polygonOffset,I.polygonOffsetFactor,I.polygonOffsetUnits),I.alphaToCoverage===!0?ht(n.SAMPLE_ALPHA_TO_COVERAGE):rt(n.SAMPLE_ALPHA_TO_COVERAGE)}function Ut(I){G!==I&&(I?n.frontFace(n.CW):n.frontFace(n.CCW),G=I)}function Ot(I){I!==Np?(ht(n.CULL_FACE),I!==x&&(I===Lu?n.cullFace(n.BACK):I===Fp?n.cullFace(n.FRONT):n.cullFace(n.FRONT_AND_BACK))):rt(n.CULL_FACE),x=I}function wt(I){I!==y&&($&&n.lineWidth(I),y=I)}function $t(I,ut,W){I?(ht(n.POLYGON_OFFSET_FILL),(V!==ut||U!==W)&&(n.polygonOffset(ut,W),V=ut,U=W)):rt(n.POLYGON_OFFSET_FILL)}function Tt(I){I?ht(n.SCISSOR_TEST):rt(n.SCISSOR_TEST)}function w(I){I===void 0&&(I=n.TEXTURE0+H-1),z!==I&&(n.activeTexture(I),z=I)}function v(I,ut,W){W===void 0&&(z===null?W=n.TEXTURE0+H-1:W=z);let K=it[W];K===void 0&&(K={type:void 0,texture:void 0},it[W]=K),(K.type!==I||K.texture!==ut)&&(z!==W&&(n.activeTexture(W),z=W),n.bindTexture(I,ut||J[I]),K.type=I,K.texture=ut)}function N(){let I=it[z];I!==void 0&&I.type!==void 0&&(n.bindTexture(I.type,null),I.type=void 0,I.texture=void 0)}function Y(){try{n.compressedTexImage2D.apply(n,arguments)}catch(I){console.error("THREE.WebGLState:",I)}}function Q(){try{n.compressedTexImage3D.apply(n,arguments)}catch(I){console.error("THREE.WebGLState:",I)}}function q(){try{n.texSubImage2D.apply(n,arguments)}catch(I){console.error("THREE.WebGLState:",I)}}function vt(){try{n.texSubImage3D.apply(n,arguments)}catch(I){console.error("THREE.WebGLState:",I)}}function st(){try{n.compressedTexSubImage2D.apply(n,arguments)}catch(I){console.error("THREE.WebGLState:",I)}}function dt(){try{n.compressedTexSubImage3D.apply(n,arguments)}catch(I){console.error("THREE.WebGLState:",I)}}function Ht(){try{n.texStorage2D.apply(n,arguments)}catch(I){console.error("THREE.WebGLState:",I)}}function j(){try{n.texStorage3D.apply(n,arguments)}catch(I){console.error("THREE.WebGLState:",I)}}function mt(){try{n.texImage2D.apply(n,arguments)}catch(I){console.error("THREE.WebGLState:",I)}}function Rt(){try{n.texImage3D.apply(n,arguments)}catch(I){console.error("THREE.WebGLState:",I)}}function Pt(I){Bt.equals(I)===!1&&(n.scissor(I.x,I.y,I.z,I.w),Bt.copy(I))}function gt(I){Vt.equals(I)===!1&&(n.viewport(I.x,I.y,I.z,I.w),Vt.copy(I))}function zt(I,ut){let W=l.get(ut);W===void 0&&(W=new WeakMap,l.set(ut,W));let K=W.get(I);K===void 0&&(K=n.getUniformBlockIndex(ut,I.name),W.set(I,K))}function Lt(I,ut){let K=l.get(ut).get(I);a.get(ut)!==K&&(n.uniformBlockBinding(ut,K,I.__bindingPointIndex),a.set(ut,K))}function Jt(){n.disable(n.BLEND),n.disable(n.CULL_FACE),n.disable(n.DEPTH_TEST),n.disable(n.POLYGON_OFFSET_FILL),n.disable(n.SCISSOR_TEST),n.disable(n.STENCIL_TEST),n.disable(n.SAMPLE_ALPHA_TO_COVERAGE),n.blendEquation(n.FUNC_ADD),n.blendFunc(n.ONE,n.ZERO),n.blendFuncSeparate(n.ONE,n.ZERO,n.ONE,n.ZERO),n.blendColor(0,0,0,0),n.colorMask(!0,!0,!0,!0),n.clearColor(0,0,0,0),n.depthMask(!0),n.depthFunc(n.LESS),n.clearDepth(1),n.stencilMask(4294967295),n.stencilFunc(n.ALWAYS,0,4294967295),n.stencilOp(n.KEEP,n.KEEP,n.KEEP),n.clearStencil(0),n.cullFace(n.BACK),n.frontFace(n.CCW),n.polygonOffset(0,0),n.activeTexture(n.TEXTURE0),n.bindFramebuffer(n.FRAMEBUFFER,null),n.bindFramebuffer(n.DRAW_FRAMEBUFFER,null),n.bindFramebuffer(n.READ_FRAMEBUFFER,null),n.useProgram(null),n.lineWidth(1),n.scissor(0,0,n.canvas.width,n.canvas.height),n.viewport(0,0,n.canvas.width,n.canvas.height),c={},z=null,it={},h={},d=new WeakMap,p=[],m=null,g=!1,_=null,u=null,f=null,b=null,S=null,E=null,C=null,A=new Gt(0,0,0),T=0,P=!1,G=null,x=null,y=null,V=null,U=null,Bt.set(0,0,n.canvas.width,n.canvas.height),Vt.set(0,0,n.canvas.width,n.canvas.height),s.reset(),r.reset(),o.reset()}return{buffers:{color:s,depth:r,stencil:o},enable:ht,disable:rt,bindFramebuffer:bt,drawBuffers:et,useProgram:at,setBlending:R,setMaterial:ie,setFlipSided:Ut,setCullFace:Ot,setLineWidth:wt,setPolygonOffset:$t,setScissorTest:Tt,activeTexture:w,bindTexture:v,unbindTexture:N,compressedTexImage2D:Y,compressedTexImage3D:Q,texImage2D:mt,texImage3D:Rt,updateUBOMapping:zt,uniformBlockBinding:Lt,texStorage2D:Ht,texStorage3D:j,texSubImage2D:q,texSubImage3D:vt,compressedTexSubImage2D:st,compressedTexSubImage3D:dt,scissor:Pt,viewport:gt,reset:Jt}}function Af(n,t,e,i){let s=dv(i);switch(e){case kf:return n*t;case Gf:return n*t;case Wf:return n*t*2;case Xf:return n*t/s.components*s.byteLength;case Ec:return n*t/s.components*s.byteLength;case qf:return n*t*2/s.components*s.byteLength;case wc:return n*t*2/s.components*s.byteLength;case Hf:return n*t*3/s.components*s.byteLength;case je:return n*t*4/s.components*s.byteLength;case Tc:return n*t*4/s.components*s.byteLength;case mo:case go:return Math.floor((n+3)/4)*Math.floor((t+3)/4)*8;case xo:case _o:return Math.floor((n+3)/4)*Math.floor((t+3)/4)*16;case _l:case yl:return Math.max(n,16)*Math.max(t,8)/4;case xl:case vl:return Math.max(n,8)*Math.max(t,8)/2;case Ml:case Sl:return Math.floor((n+3)/4)*Math.floor((t+3)/4)*8;case bl:return Math.floor((n+3)/4)*Math.floor((t+3)/4)*16;case El:return Math.floor((n+3)/4)*Math.floor((t+3)/4)*16;case wl:return Math.floor((n+4)/5)*Math.floor((t+3)/4)*16;case Tl:return Math.floor((n+4)/5)*Math.floor((t+4)/5)*16;case Al:return Math.floor((n+5)/6)*Math.floor((t+4)/5)*16;case Cl:return Math.floor((n+5)/6)*Math.floor((t+5)/6)*16;case Rl:return Math.floor((n+7)/8)*Math.floor((t+4)/5)*16;case Pl:return Math.floor((n+7)/8)*Math.floor((t+5)/6)*16;case Il:return Math.floor((n+7)/8)*Math.floor((t+7)/8)*16;case Ll:return Math.floor((n+9)/10)*Math.floor((t+4)/5)*16;case Dl:return Math.floor((n+9)/10)*Math.floor((t+5)/6)*16;case Ul:return Math.floor((n+9)/10)*Math.floor((t+7)/8)*16;case Nl:return Math.floor((n+9)/10)*Math.floor((t+9)/10)*16;case Fl:return Math.floor((n+11)/12)*Math.floor((t+9)/10)*16;case Ol:return Math.floor((n+11)/12)*Math.floor((t+11)/12)*16;case vo:case Bl:case Vl:return Math.ceil(n/4)*Math.ceil(t/4)*16;case Yf:case zl:return Math.ceil(n/4)*Math.ceil(t/4)*8;case kl:case Hl:return Math.ceil(n/4)*Math.ceil(t/4)*16}throw new Error(`Unable to determine texture byte length for ${e} format.`)}function dv(n){switch(n){case bn:case Bf:return{byteLength:1,components:1};case zs:case Vf:case Xs:return{byteLength:2,components:1};case Sc:case bc:return{byteLength:2,components:4};case pi:case Mc:case Mn:return{byteLength:4,components:1};case zf:return{byteLength:4,components:3}}throw new Error(`Unknown texture type ${n}.`)}function pv(n,t,e,i,s,r,o){let a=t.has("WEBGL_multisampled_render_to_texture")?t.get("WEBGL_multisampled_render_to_texture"):null,l=typeof navigator=="undefined"?!1:/OculusBrowser/g.test(navigator.userAgent),c=new Zt,h=new WeakMap,d,p=new WeakMap,m=!1;try{m=typeof OffscreenCanvas!="undefined"&&new OffscreenCanvas(1,1).getContext("2d")!==null}catch{}function g(w,v){return m?new OffscreenCanvas(w,v):Ao("canvas")}function _(w,v,N){let Y=1,Q=Tt(w);if((Q.width>N||Q.height>N)&&(Y=N/Math.max(Q.width,Q.height)),Y<1)if(typeof HTMLImageElement!="undefined"&&w instanceof HTMLImageElement||typeof HTMLCanvasElement!="undefined"&&w instanceof HTMLCanvasElement||typeof ImageBitmap!="undefined"&&w instanceof ImageBitmap||typeof VideoFrame!="undefined"&&w instanceof VideoFrame){let q=Math.floor(Y*Q.width),vt=Math.floor(Y*Q.height);d===void 0&&(d=g(q,vt));let st=v?g(q,vt):d;return st.width=q,st.height=vt,st.getContext("2d").drawImage(w,0,0,q,vt),console.warn("THREE.WebGLRenderer: Texture has been resized from ("+Q.width+"x"+Q.height+") to ("+q+"x"+vt+")."),st}else return"data"in w&&console.warn("THREE.WebGLRenderer: Image in DataTexture is too big ("+Q.width+"x"+Q.height+")."),w;return w}function u(w){return w.generateMipmaps&&w.minFilter!==We&&w.minFilter!==Qe}function f(w){n.generateMipmap(w)}function b(w,v,N,Y,Q=!1){if(w!==null){if(n[w]!==void 0)return n[w];console.warn("THREE.WebGLRenderer: Attempt to use non-existing WebGL internal format '"+w+"'")}let q=v;if(v===n.RED&&(N===n.FLOAT&&(q=n.R32F),N===n.HALF_FLOAT&&(q=n.R16F),N===n.UNSIGNED_BYTE&&(q=n.R8)),v===n.RED_INTEGER&&(N===n.UNSIGNED_BYTE&&(q=n.R8UI),N===n.UNSIGNED_SHORT&&(q=n.R16UI),N===n.UNSIGNED_INT&&(q=n.R32UI),N===n.BYTE&&(q=n.R8I),N===n.SHORT&&(q=n.R16I),N===n.INT&&(q=n.R32I)),v===n.RG&&(N===n.FLOAT&&(q=n.RG32F),N===n.HALF_FLOAT&&(q=n.RG16F),N===n.UNSIGNED_BYTE&&(q=n.RG8)),v===n.RG_INTEGER&&(N===n.UNSIGNED_BYTE&&(q=n.RG8UI),N===n.UNSIGNED_SHORT&&(q=n.RG16UI),N===n.UNSIGNED_INT&&(q=n.RG32UI),N===n.BYTE&&(q=n.RG8I),N===n.SHORT&&(q=n.RG16I),N===n.INT&&(q=n.RG32I)),v===n.RGB_INTEGER&&(N===n.UNSIGNED_BYTE&&(q=n.RGB8UI),N===n.UNSIGNED_SHORT&&(q=n.RGB16UI),N===n.UNSIGNED_INT&&(q=n.RGB32UI),N===n.BYTE&&(q=n.RGB8I),N===n.SHORT&&(q=n.RGB16I),N===n.INT&&(q=n.RGB32I)),v===n.RGBA_INTEGER&&(N===n.UNSIGNED_BYTE&&(q=n.RGBA8UI),N===n.UNSIGNED_SHORT&&(q=n.RGBA16UI),N===n.UNSIGNED_INT&&(q=n.RGBA32UI),N===n.BYTE&&(q=n.RGBA8I),N===n.SHORT&&(q=n.RGBA16I),N===n.INT&&(q=n.RGBA32I)),v===n.RGB&&N===n.UNSIGNED_INT_5_9_9_9_REV&&(q=n.RGB9_E5),v===n.RGBA){let vt=Q?bo:Yt.getTransfer(Y);N===n.FLOAT&&(q=n.RGBA32F),N===n.HALF_FLOAT&&(q=n.RGBA16F),N===n.UNSIGNED_BYTE&&(q=vt===te?n.SRGB8_ALPHA8:n.RGBA8),N===n.UNSIGNED_SHORT_4_4_4_4&&(q=n.RGBA4),N===n.UNSIGNED_SHORT_5_5_5_1&&(q=n.RGB5_A1)}return(q===n.R16F||q===n.R32F||q===n.RG16F||q===n.RG32F||q===n.RGBA16F||q===n.RGBA32F)&&t.get("EXT_color_buffer_float"),q}function S(w,v){let N;return w?v===null||v===pi||v===us?N=n.DEPTH24_STENCIL8:v===Mn?N=n.DEPTH32F_STENCIL8:v===zs&&(N=n.DEPTH24_STENCIL8,console.warn("DepthTexture: 16 bit depth attachment is not supported with stencil. Using 24-bit attachment.")):v===null||v===pi||v===us?N=n.DEPTH_COMPONENT24:v===Mn?N=n.DEPTH_COMPONENT32F:v===zs&&(N=n.DEPTH_COMPONENT16),N}function E(w,v){return u(w)===!0||w.isFramebufferTexture&&w.minFilter!==We&&w.minFilter!==Qe?Math.log2(Math.max(v.width,v.height))+1:w.mipmaps!==void 0&&w.mipmaps.length>0?w.mipmaps.length:w.isCompressedTexture&&Array.isArray(w.image)?v.mipmaps.length:1}function C(w){let v=w.target;v.removeEventListener("dispose",C),T(v),v.isVideoTexture&&h.delete(v)}function A(w){let v=w.target;v.removeEventListener("dispose",A),G(v)}function T(w){let v=i.get(w);if(v.__webglInit===void 0)return;let N=w.source,Y=p.get(N);if(Y){let Q=Y[v.__cacheKey];Q.usedTimes--,Q.usedTimes===0&&P(w),Object.keys(Y).length===0&&p.delete(N)}i.remove(w)}function P(w){let v=i.get(w);n.deleteTexture(v.__webglTexture);let N=w.source,Y=p.get(N);delete Y[v.__cacheKey],o.memory.textures--}function G(w){let v=i.get(w);if(w.depthTexture&&w.depthTexture.dispose(),w.isWebGLCubeRenderTarget)for(let Y=0;Y<6;Y++){if(Array.isArray(v.__webglFramebuffer[Y]))for(let Q=0;Q<v.__webglFramebuffer[Y].length;Q++)n.deleteFramebuffer(v.__webglFramebuffer[Y][Q]);else n.deleteFramebuffer(v.__webglFramebuffer[Y]);v.__webglDepthbuffer&&n.deleteRenderbuffer(v.__webglDepthbuffer[Y])}else{if(Array.isArray(v.__webglFramebuffer))for(let Y=0;Y<v.__webglFramebuffer.length;Y++)n.deleteFramebuffer(v.__webglFramebuffer[Y]);else n.deleteFramebuffer(v.__webglFramebuffer);if(v.__webglDepthbuffer&&n.deleteRenderbuffer(v.__webglDepthbuffer),v.__webglMultisampledFramebuffer&&n.deleteFramebuffer(v.__webglMultisampledFramebuffer),v.__webglColorRenderbuffer)for(let Y=0;Y<v.__webglColorRenderbuffer.length;Y++)v.__webglColorRenderbuffer[Y]&&n.deleteRenderbuffer(v.__webglColorRenderbuffer[Y]);v.__webglDepthRenderbuffer&&n.deleteRenderbuffer(v.__webglDepthRenderbuffer)}let N=w.textures;for(let Y=0,Q=N.length;Y<Q;Y++){let q=i.get(N[Y]);q.__webglTexture&&(n.deleteTexture(q.__webglTexture),o.memory.textures--),i.remove(N[Y])}i.remove(w)}let x=0;function y(){x=0}function V(){let w=x;return w>=s.maxTextures&&console.warn("THREE.WebGLTextures: Trying to use "+w+" texture units while this GPU supports only "+s.maxTextures),x+=1,w}function U(w){let v=[];return v.push(w.wrapS),v.push(w.wrapT),v.push(w.wrapR||0),v.push(w.magFilter),v.push(w.minFilter),v.push(w.anisotropy),v.push(w.internalFormat),v.push(w.format),v.push(w.type),v.push(w.generateMipmaps),v.push(w.premultiplyAlpha),v.push(w.flipY),v.push(w.unpackAlignment),v.push(w.colorSpace),v.join()}function H(w,v){let N=i.get(w);if(w.isVideoTexture&&wt(w),w.isRenderTargetTexture===!1&&w.version>0&&N.__version!==w.version){let Y=w.image;if(Y===null)console.warn("THREE.WebGLRenderer: Texture marked for update but no image data found.");else if(Y.complete===!1)console.warn("THREE.WebGLRenderer: Texture marked for update but image is incomplete");else{Vt(N,w,v);return}}e.bindTexture(n.TEXTURE_2D,N.__webglTexture,n.TEXTURE0+v)}function $(w,v){let N=i.get(w);if(w.version>0&&N.__version!==w.version){Vt(N,w,v);return}e.bindTexture(n.TEXTURE_2D_ARRAY,N.__webglTexture,n.TEXTURE0+v)}function k(w,v){let N=i.get(w);if(w.version>0&&N.__version!==w.version){Vt(N,w,v);return}e.bindTexture(n.TEXTURE_3D,N.__webglTexture,n.TEXTURE0+v)}function Z(w,v){let N=i.get(w);if(w.version>0&&N.__version!==w.version){X(N,w,v);return}e.bindTexture(n.TEXTURE_CUBE_MAP,N.__webglTexture,n.TEXTURE0+v)}let z={[ml]:n.REPEAT,[fi]:n.CLAMP_TO_EDGE,[gl]:n.MIRRORED_REPEAT},it={[We]:n.NEAREST,[um]:n.NEAREST_MIPMAP_NEAREST,[Hr]:n.NEAREST_MIPMAP_LINEAR,[Qe]:n.LINEAR,[Ta]:n.LINEAR_MIPMAP_NEAREST,[di]:n.LINEAR_MIPMAP_LINEAR},ot={[gm]:n.NEVER,[Sm]:n.ALWAYS,[xm]:n.LESS,[Kf]:n.LEQUAL,[_m]:n.EQUAL,[Mm]:n.GEQUAL,[vm]:n.GREATER,[ym]:n.NOTEQUAL};function pt(w,v){if(v.type===Mn&&t.has("OES_texture_float_linear")===!1&&(v.magFilter===Qe||v.magFilter===Ta||v.magFilter===Hr||v.magFilter===di||v.minFilter===Qe||v.minFilter===Ta||v.minFilter===Hr||v.minFilter===di)&&console.warn("THREE.WebGLRenderer: Unable to use linear filtering with floating point textures. OES_texture_float_linear not supported on this device."),n.texParameteri(w,n.TEXTURE_WRAP_S,z[v.wrapS]),n.texParameteri(w,n.TEXTURE_WRAP_T,z[v.wrapT]),(w===n.TEXTURE_3D||w===n.TEXTURE_2D_ARRAY)&&n.texParameteri(w,n.TEXTURE_WRAP_R,z[v.wrapR]),n.texParameteri(w,n.TEXTURE_MAG_FILTER,it[v.magFilter]),n.texParameteri(w,n.TEXTURE_MIN_FILTER,it[v.minFilter]),v.compareFunction&&(n.texParameteri(w,n.TEXTURE_COMPARE_MODE,n.COMPARE_REF_TO_TEXTURE),n.texParameteri(w,n.TEXTURE_COMPARE_FUNC,ot[v.compareFunction])),t.has("EXT_texture_filter_anisotropic")===!0){if(v.magFilter===We||v.minFilter!==Hr&&v.minFilter!==di||v.type===Mn&&t.has("OES_texture_float_linear")===!1)return;if(v.anisotropy>1||i.get(v).__currentAnisotropy){let N=t.get("EXT_texture_filter_anisotropic");n.texParameterf(w,N.TEXTURE_MAX_ANISOTROPY_EXT,Math.min(v.anisotropy,s.getMaxAnisotropy())),i.get(v).__currentAnisotropy=v.anisotropy}}}function Bt(w,v){let N=!1;w.__webglInit===void 0&&(w.__webglInit=!0,v.addEventListener("dispose",C));let Y=v.source,Q=p.get(Y);Q===void 0&&(Q={},p.set(Y,Q));let q=U(v);if(q!==w.__cacheKey){Q[q]===void 0&&(Q[q]={texture:n.createTexture(),usedTimes:0},o.memory.textures++,N=!0),Q[q].usedTimes++;let vt=Q[w.__cacheKey];vt!==void 0&&(Q[w.__cacheKey].usedTimes--,vt.usedTimes===0&&P(v)),w.__cacheKey=q,w.__webglTexture=Q[q].texture}return N}function Vt(w,v,N){let Y=n.TEXTURE_2D;(v.isDataArrayTexture||v.isCompressedArrayTexture)&&(Y=n.TEXTURE_2D_ARRAY),v.isData3DTexture&&(Y=n.TEXTURE_3D);let Q=Bt(w,v),q=v.source;e.bindTexture(Y,w.__webglTexture,n.TEXTURE0+N);let vt=i.get(q);if(q.version!==vt.__version||Q===!0){e.activeTexture(n.TEXTURE0+N);let st=Yt.getPrimaries(Yt.workingColorSpace),dt=v.colorSpace===Hn?null:Yt.getPrimaries(v.colorSpace),Ht=v.colorSpace===Hn||st===dt?n.NONE:n.BROWSER_DEFAULT_WEBGL;n.pixelStorei(n.UNPACK_FLIP_Y_WEBGL,v.flipY),n.pixelStorei(n.UNPACK_PREMULTIPLY_ALPHA_WEBGL,v.premultiplyAlpha),n.pixelStorei(n.UNPACK_ALIGNMENT,v.unpackAlignment),n.pixelStorei(n.UNPACK_COLORSPACE_CONVERSION_WEBGL,Ht);let j=_(v.image,!1,s.maxTextureSize);j=$t(v,j);let mt=r.convert(v.format,v.colorSpace),Rt=r.convert(v.type),Pt=b(v.internalFormat,mt,Rt,v.colorSpace,v.isVideoTexture);pt(Y,v);let gt,zt=v.mipmaps,Lt=v.isVideoTexture!==!0,Jt=vt.__version===void 0||Q===!0,I=q.dataReady,ut=E(v,j);if(v.isDepthTexture)Pt=S(v.format===fs,v.type),Jt&&(Lt?e.texStorage2D(n.TEXTURE_2D,1,Pt,j.width,j.height):e.texImage2D(n.TEXTURE_2D,0,Pt,j.width,j.height,0,mt,Rt,null));else if(v.isDataTexture)if(zt.length>0){Lt&&Jt&&e.texStorage2D(n.TEXTURE_2D,ut,Pt,zt[0].width,zt[0].height);for(let W=0,K=zt.length;W<K;W++)gt=zt[W],Lt?I&&e.texSubImage2D(n.TEXTURE_2D,W,0,0,gt.width,gt.height,mt,Rt,gt.data):e.texImage2D(n.TEXTURE_2D,W,Pt,gt.width,gt.height,0,mt,Rt,gt.data);v.generateMipmaps=!1}else Lt?(Jt&&e.texStorage2D(n.TEXTURE_2D,ut,Pt,j.width,j.height),I&&e.texSubImage2D(n.TEXTURE_2D,0,0,0,j.width,j.height,mt,Rt,j.data)):e.texImage2D(n.TEXTURE_2D,0,Pt,j.width,j.height,0,mt,Rt,j.data);else if(v.isCompressedTexture)if(v.isCompressedArrayTexture){Lt&&Jt&&e.texStorage3D(n.TEXTURE_2D_ARRAY,ut,Pt,zt[0].width,zt[0].height,j.depth);for(let W=0,K=zt.length;W<K;W++)if(gt=zt[W],v.format!==je)if(mt!==null)if(Lt){if(I)if(v.layerUpdates.size>0){let lt=Af(gt.width,gt.height,v.format,v.type);for(let ft of v.layerUpdates){let kt=gt.data.subarray(ft*lt/gt.data.BYTES_PER_ELEMENT,(ft+1)*lt/gt.data.BYTES_PER_ELEMENT);e.compressedTexSubImage3D(n.TEXTURE_2D_ARRAY,W,0,0,ft,gt.width,gt.height,1,mt,kt,0,0)}v.clearLayerUpdates()}else e.compressedTexSubImage3D(n.TEXTURE_2D_ARRAY,W,0,0,0,gt.width,gt.height,j.depth,mt,gt.data,0,0)}else e.compressedTexImage3D(n.TEXTURE_2D_ARRAY,W,Pt,gt.width,gt.height,j.depth,0,gt.data,0,0);else console.warn("THREE.WebGLRenderer: Attempt to load unsupported compressed texture format in .uploadTexture()");else Lt?I&&e.texSubImage3D(n.TEXTURE_2D_ARRAY,W,0,0,0,gt.width,gt.height,j.depth,mt,Rt,gt.data):e.texImage3D(n.TEXTURE_2D_ARRAY,W,Pt,gt.width,gt.height,j.depth,0,mt,Rt,gt.data)}else{Lt&&Jt&&e.texStorage2D(n.TEXTURE_2D,ut,Pt,zt[0].width,zt[0].height);for(let W=0,K=zt.length;W<K;W++)gt=zt[W],v.format!==je?mt!==null?Lt?I&&e.compressedTexSubImage2D(n.TEXTURE_2D,W,0,0,gt.width,gt.height,mt,gt.data):e.compressedTexImage2D(n.TEXTURE_2D,W,Pt,gt.width,gt.height,0,gt.data):console.warn("THREE.WebGLRenderer: Attempt to load unsupported compressed texture format in .uploadTexture()"):Lt?I&&e.texSubImage2D(n.TEXTURE_2D,W,0,0,gt.width,gt.height,mt,Rt,gt.data):e.texImage2D(n.TEXTURE_2D,W,Pt,gt.width,gt.height,0,mt,Rt,gt.data)}else if(v.isDataArrayTexture)if(Lt){if(Jt&&e.texStorage3D(n.TEXTURE_2D_ARRAY,ut,Pt,j.width,j.height,j.depth),I)if(v.layerUpdates.size>0){let W=Af(j.width,j.height,v.format,v.type);for(let K of v.layerUpdates){let lt=j.data.subarray(K*W/j.data.BYTES_PER_ELEMENT,(K+1)*W/j.data.BYTES_PER_ELEMENT);e.texSubImage3D(n.TEXTURE_2D_ARRAY,0,0,0,K,j.width,j.height,1,mt,Rt,lt)}v.clearLayerUpdates()}else e.texSubImage3D(n.TEXTURE_2D_ARRAY,0,0,0,0,j.width,j.height,j.depth,mt,Rt,j.data)}else e.texImage3D(n.TEXTURE_2D_ARRAY,0,Pt,j.width,j.height,j.depth,0,mt,Rt,j.data);else if(v.isData3DTexture)Lt?(Jt&&e.texStorage3D(n.TEXTURE_3D,ut,Pt,j.width,j.height,j.depth),I&&e.texSubImage3D(n.TEXTURE_3D,0,0,0,0,j.width,j.height,j.depth,mt,Rt,j.data)):e.texImage3D(n.TEXTURE_3D,0,Pt,j.width,j.height,j.depth,0,mt,Rt,j.data);else if(v.isFramebufferTexture){if(Jt)if(Lt)e.texStorage2D(n.TEXTURE_2D,ut,Pt,j.width,j.height);else{let W=j.width,K=j.height;for(let lt=0;lt<ut;lt++)e.texImage2D(n.TEXTURE_2D,lt,Pt,W,K,0,mt,Rt,null),W>>=1,K>>=1}}else if(zt.length>0){if(Lt&&Jt){let W=Tt(zt[0]);e.texStorage2D(n.TEXTURE_2D,ut,Pt,W.width,W.height)}for(let W=0,K=zt.length;W<K;W++)gt=zt[W],Lt?I&&e.texSubImage2D(n.TEXTURE_2D,W,0,0,mt,Rt,gt):e.texImage2D(n.TEXTURE_2D,W,Pt,mt,Rt,gt);v.generateMipmaps=!1}else if(Lt){if(Jt){let W=Tt(j);e.texStorage2D(n.TEXTURE_2D,ut,Pt,W.width,W.height)}I&&e.texSubImage2D(n.TEXTURE_2D,0,0,0,mt,Rt,j)}else e.texImage2D(n.TEXTURE_2D,0,Pt,mt,Rt,j);u(v)&&f(Y),vt.__version=q.version,v.onUpdate&&v.onUpdate(v)}w.__version=v.version}function X(w,v,N){if(v.image.length!==6)return;let Y=Bt(w,v),Q=v.source;e.bindTexture(n.TEXTURE_CUBE_MAP,w.__webglTexture,n.TEXTURE0+N);let q=i.get(Q);if(Q.version!==q.__version||Y===!0){e.activeTexture(n.TEXTURE0+N);let vt=Yt.getPrimaries(Yt.workingColorSpace),st=v.colorSpace===Hn?null:Yt.getPrimaries(v.colorSpace),dt=v.colorSpace===Hn||vt===st?n.NONE:n.BROWSER_DEFAULT_WEBGL;n.pixelStorei(n.UNPACK_FLIP_Y_WEBGL,v.flipY),n.pixelStorei(n.UNPACK_PREMULTIPLY_ALPHA_WEBGL,v.premultiplyAlpha),n.pixelStorei(n.UNPACK_ALIGNMENT,v.unpackAlignment),n.pixelStorei(n.UNPACK_COLORSPACE_CONVERSION_WEBGL,dt);let Ht=v.isCompressedTexture||v.image[0].isCompressedTexture,j=v.image[0]&&v.image[0].isDataTexture,mt=[];for(let K=0;K<6;K++)!Ht&&!j?mt[K]=_(v.image[K],!0,s.maxCubemapSize):mt[K]=j?v.image[K].image:v.image[K],mt[K]=$t(v,mt[K]);let Rt=mt[0],Pt=r.convert(v.format,v.colorSpace),gt=r.convert(v.type),zt=b(v.internalFormat,Pt,gt,v.colorSpace),Lt=v.isVideoTexture!==!0,Jt=q.__version===void 0||Y===!0,I=Q.dataReady,ut=E(v,Rt);pt(n.TEXTURE_CUBE_MAP,v);let W;if(Ht){Lt&&Jt&&e.texStorage2D(n.TEXTURE_CUBE_MAP,ut,zt,Rt.width,Rt.height);for(let K=0;K<6;K++){W=mt[K].mipmaps;for(let lt=0;lt<W.length;lt++){let ft=W[lt];v.format!==je?Pt!==null?Lt?I&&e.compressedTexSubImage2D(n.TEXTURE_CUBE_MAP_POSITIVE_X+K,lt,0,0,ft.width,ft.height,Pt,ft.data):e.compressedTexImage2D(n.TEXTURE_CUBE_MAP_POSITIVE_X+K,lt,zt,ft.width,ft.height,0,ft.data):console.warn("THREE.WebGLRenderer: Attempt to load unsupported compressed texture format in .setTextureCube()"):Lt?I&&e.texSubImage2D(n.TEXTURE_CUBE_MAP_POSITIVE_X+K,lt,0,0,ft.width,ft.height,Pt,gt,ft.data):e.texImage2D(n.TEXTURE_CUBE_MAP_POSITIVE_X+K,lt,zt,ft.width,ft.height,0,Pt,gt,ft.data)}}}else{if(W=v.mipmaps,Lt&&Jt){W.length>0&&ut++;let K=Tt(mt[0]);e.texStorage2D(n.TEXTURE_CUBE_MAP,ut,zt,K.width,K.height)}for(let K=0;K<6;K++)if(j){Lt?I&&e.texSubImage2D(n.TEXTURE_CUBE_MAP_POSITIVE_X+K,0,0,0,mt[K].width,mt[K].height,Pt,gt,mt[K].data):e.texImage2D(n.TEXTURE_CUBE_MAP_POSITIVE_X+K,0,zt,mt[K].width,mt[K].height,0,Pt,gt,mt[K].data);for(let lt=0;lt<W.length;lt++){let kt=W[lt].image[K].image;Lt?I&&e.texSubImage2D(n.TEXTURE_CUBE_MAP_POSITIVE_X+K,lt+1,0,0,kt.width,kt.height,Pt,gt,kt.data):e.texImage2D(n.TEXTURE_CUBE_MAP_POSITIVE_X+K,lt+1,zt,kt.width,kt.height,0,Pt,gt,kt.data)}}else{Lt?I&&e.texSubImage2D(n.TEXTURE_CUBE_MAP_POSITIVE_X+K,0,0,0,Pt,gt,mt[K]):e.texImage2D(n.TEXTURE_CUBE_MAP_POSITIVE_X+K,0,zt,Pt,gt,mt[K]);for(let lt=0;lt<W.length;lt++){let ft=W[lt];Lt?I&&e.texSubImage2D(n.TEXTURE_CUBE_MAP_POSITIVE_X+K,lt+1,0,0,Pt,gt,ft.image[K]):e.texImage2D(n.TEXTURE_CUBE_MAP_POSITIVE_X+K,lt+1,zt,Pt,gt,ft.image[K])}}}u(v)&&f(n.TEXTURE_CUBE_MAP),q.__version=Q.version,v.onUpdate&&v.onUpdate(v)}w.__version=v.version}function J(w,v,N,Y,Q,q){let vt=r.convert(N.format,N.colorSpace),st=r.convert(N.type),dt=b(N.internalFormat,vt,st,N.colorSpace);if(!i.get(v).__hasExternalTextures){let j=Math.max(1,v.width>>q),mt=Math.max(1,v.height>>q);Q===n.TEXTURE_3D||Q===n.TEXTURE_2D_ARRAY?e.texImage3D(Q,q,dt,j,mt,v.depth,0,vt,st,null):e.texImage2D(Q,q,dt,j,mt,0,vt,st,null)}e.bindFramebuffer(n.FRAMEBUFFER,w),Ot(v)?a.framebufferTexture2DMultisampleEXT(n.FRAMEBUFFER,Y,Q,i.get(N).__webglTexture,0,Ut(v)):(Q===n.TEXTURE_2D||Q>=n.TEXTURE_CUBE_MAP_POSITIVE_X&&Q<=n.TEXTURE_CUBE_MAP_NEGATIVE_Z)&&n.framebufferTexture2D(n.FRAMEBUFFER,Y,Q,i.get(N).__webglTexture,q),e.bindFramebuffer(n.FRAMEBUFFER,null)}function ht(w,v,N){if(n.bindRenderbuffer(n.RENDERBUFFER,w),v.depthBuffer){let Y=v.depthTexture,Q=Y&&Y.isDepthTexture?Y.type:null,q=S(v.stencilBuffer,Q),vt=v.stencilBuffer?n.DEPTH_STENCIL_ATTACHMENT:n.DEPTH_ATTACHMENT,st=Ut(v);Ot(v)?a.renderbufferStorageMultisampleEXT(n.RENDERBUFFER,st,q,v.width,v.height):N?n.renderbufferStorageMultisample(n.RENDERBUFFER,st,q,v.width,v.height):n.renderbufferStorage(n.RENDERBUFFER,q,v.width,v.height),n.framebufferRenderbuffer(n.FRAMEBUFFER,vt,n.RENDERBUFFER,w)}else{let Y=v.textures;for(let Q=0;Q<Y.length;Q++){let q=Y[Q],vt=r.convert(q.format,q.colorSpace),st=r.convert(q.type),dt=b(q.internalFormat,vt,st,q.colorSpace),Ht=Ut(v);N&&Ot(v)===!1?n.renderbufferStorageMultisample(n.RENDERBUFFER,Ht,dt,v.width,v.height):Ot(v)?a.renderbufferStorageMultisampleEXT(n.RENDERBUFFER,Ht,dt,v.width,v.height):n.renderbufferStorage(n.RENDERBUFFER,dt,v.width,v.height)}}n.bindRenderbuffer(n.RENDERBUFFER,null)}function rt(w,v){if(v&&v.isWebGLCubeRenderTarget)throw new Error("Depth Texture with cube render targets is not supported");if(e.bindFramebuffer(n.FRAMEBUFFER,w),!(v.depthTexture&&v.depthTexture.isDepthTexture))throw new Error("renderTarget.depthTexture must be an instance of THREE.DepthTexture");(!i.get(v.depthTexture).__webglTexture||v.depthTexture.image.width!==v.width||v.depthTexture.image.height!==v.height)&&(v.depthTexture.image.width=v.width,v.depthTexture.image.height=v.height,v.depthTexture.needsUpdate=!0),H(v.depthTexture,0);let Y=i.get(v.depthTexture).__webglTexture,Q=Ut(v);if(v.depthTexture.format===rs)Ot(v)?a.framebufferTexture2DMultisampleEXT(n.FRAMEBUFFER,n.DEPTH_ATTACHMENT,n.TEXTURE_2D,Y,0,Q):n.framebufferTexture2D(n.FRAMEBUFFER,n.DEPTH_ATTACHMENT,n.TEXTURE_2D,Y,0);else if(v.depthTexture.format===fs)Ot(v)?a.framebufferTexture2DMultisampleEXT(n.FRAMEBUFFER,n.DEPTH_STENCIL_ATTACHMENT,n.TEXTURE_2D,Y,0,Q):n.framebufferTexture2D(n.FRAMEBUFFER,n.DEPTH_STENCIL_ATTACHMENT,n.TEXTURE_2D,Y,0);else throw new Error("Unknown depthTexture format")}function bt(w){let v=i.get(w),N=w.isWebGLCubeRenderTarget===!0;if(v.__boundDepthTexture!==w.depthTexture){let Y=w.depthTexture;if(v.__depthDisposeCallback&&v.__depthDisposeCallback(),Y){let Q=()=>{delete v.__boundDepthTexture,delete v.__depthDisposeCallback,Y.removeEventListener("dispose",Q)};Y.addEventListener("dispose",Q),v.__depthDisposeCallback=Q}v.__boundDepthTexture=Y}if(w.depthTexture&&!v.__autoAllocateDepthBuffer){if(N)throw new Error("target.depthTexture not supported in Cube render targets");rt(v.__webglFramebuffer,w)}else if(N){v.__webglDepthbuffer=[];for(let Y=0;Y<6;Y++)if(e.bindFramebuffer(n.FRAMEBUFFER,v.__webglFramebuffer[Y]),v.__webglDepthbuffer[Y]===void 0)v.__webglDepthbuffer[Y]=n.createRenderbuffer(),ht(v.__webglDepthbuffer[Y],w,!1);else{let Q=w.stencilBuffer?n.DEPTH_STENCIL_ATTACHMENT:n.DEPTH_ATTACHMENT,q=v.__webglDepthbuffer[Y];n.bindRenderbuffer(n.RENDERBUFFER,q),n.framebufferRenderbuffer(n.FRAMEBUFFER,Q,n.RENDERBUFFER,q)}}else if(e.bindFramebuffer(n.FRAMEBUFFER,v.__webglFramebuffer),v.__webglDepthbuffer===void 0)v.__webglDepthbuffer=n.createRenderbuffer(),ht(v.__webglDepthbuffer,w,!1);else{let Y=w.stencilBuffer?n.DEPTH_STENCIL_ATTACHMENT:n.DEPTH_ATTACHMENT,Q=v.__webglDepthbuffer;n.bindRenderbuffer(n.RENDERBUFFER,Q),n.framebufferRenderbuffer(n.FRAMEBUFFER,Y,n.RENDERBUFFER,Q)}e.bindFramebuffer(n.FRAMEBUFFER,null)}function et(w,v,N){let Y=i.get(w);v!==void 0&&J(Y.__webglFramebuffer,w,w.texture,n.COLOR_ATTACHMENT0,n.TEXTURE_2D,0),N!==void 0&&bt(w)}function at(w){let v=w.texture,N=i.get(w),Y=i.get(v);w.addEventListener("dispose",A);let Q=w.textures,q=w.isWebGLCubeRenderTarget===!0,vt=Q.length>1;if(vt||(Y.__webglTexture===void 0&&(Y.__webglTexture=n.createTexture()),Y.__version=v.version,o.memory.textures++),q){N.__webglFramebuffer=[];for(let st=0;st<6;st++)if(v.mipmaps&&v.mipmaps.length>0){N.__webglFramebuffer[st]=[];for(let dt=0;dt<v.mipmaps.length;dt++)N.__webglFramebuffer[st][dt]=n.createFramebuffer()}else N.__webglFramebuffer[st]=n.createFramebuffer()}else{if(v.mipmaps&&v.mipmaps.length>0){N.__webglFramebuffer=[];for(let st=0;st<v.mipmaps.length;st++)N.__webglFramebuffer[st]=n.createFramebuffer()}else N.__webglFramebuffer=n.createFramebuffer();if(vt)for(let st=0,dt=Q.length;st<dt;st++){let Ht=i.get(Q[st]);Ht.__webglTexture===void 0&&(Ht.__webglTexture=n.createTexture(),o.memory.textures++)}if(w.samples>0&&Ot(w)===!1){N.__webglMultisampledFramebuffer=n.createFramebuffer(),N.__webglColorRenderbuffer=[],e.bindFramebuffer(n.FRAMEBUFFER,N.__webglMultisampledFramebuffer);for(let st=0;st<Q.length;st++){let dt=Q[st];N.__webglColorRenderbuffer[st]=n.createRenderbuffer(),n.bindRenderbuffer(n.RENDERBUFFER,N.__webglColorRenderbuffer[st]);let Ht=r.convert(dt.format,dt.colorSpace),j=r.convert(dt.type),mt=b(dt.internalFormat,Ht,j,dt.colorSpace,w.isXRRenderTarget===!0),Rt=Ut(w);n.renderbufferStorageMultisample(n.RENDERBUFFER,Rt,mt,w.width,w.height),n.framebufferRenderbuffer(n.FRAMEBUFFER,n.COLOR_ATTACHMENT0+st,n.RENDERBUFFER,N.__webglColorRenderbuffer[st])}n.bindRenderbuffer(n.RENDERBUFFER,null),w.depthBuffer&&(N.__webglDepthRenderbuffer=n.createRenderbuffer(),ht(N.__webglDepthRenderbuffer,w,!0)),e.bindFramebuffer(n.FRAMEBUFFER,null)}}if(q){e.bindTexture(n.TEXTURE_CUBE_MAP,Y.__webglTexture),pt(n.TEXTURE_CUBE_MAP,v);for(let st=0;st<6;st++)if(v.mipmaps&&v.mipmaps.length>0)for(let dt=0;dt<v.mipmaps.length;dt++)J(N.__webglFramebuffer[st][dt],w,v,n.COLOR_ATTACHMENT0,n.TEXTURE_CUBE_MAP_POSITIVE_X+st,dt);else J(N.__webglFramebuffer[st],w,v,n.COLOR_ATTACHMENT0,n.TEXTURE_CUBE_MAP_POSITIVE_X+st,0);u(v)&&f(n.TEXTURE_CUBE_MAP),e.unbindTexture()}else if(vt){for(let st=0,dt=Q.length;st<dt;st++){let Ht=Q[st],j=i.get(Ht);e.bindTexture(n.TEXTURE_2D,j.__webglTexture),pt(n.TEXTURE_2D,Ht),J(N.__webglFramebuffer,w,Ht,n.COLOR_ATTACHMENT0+st,n.TEXTURE_2D,0),u(Ht)&&f(n.TEXTURE_2D)}e.unbindTexture()}else{let st=n.TEXTURE_2D;if((w.isWebGL3DRenderTarget||w.isWebGLArrayRenderTarget)&&(st=w.isWebGL3DRenderTarget?n.TEXTURE_3D:n.TEXTURE_2D_ARRAY),e.bindTexture(st,Y.__webglTexture),pt(st,v),v.mipmaps&&v.mipmaps.length>0)for(let dt=0;dt<v.mipmaps.length;dt++)J(N.__webglFramebuffer[dt],w,v,n.COLOR_ATTACHMENT0,st,dt);else J(N.__webglFramebuffer,w,v,n.COLOR_ATTACHMENT0,st,0);u(v)&&f(st),e.unbindTexture()}w.depthBuffer&&bt(w)}function Et(w){let v=w.textures;for(let N=0,Y=v.length;N<Y;N++){let Q=v[N];if(u(Q)){let q=w.isWebGLCubeRenderTarget?n.TEXTURE_CUBE_MAP:n.TEXTURE_2D,vt=i.get(Q).__webglTexture;e.bindTexture(q,vt),f(q),e.unbindTexture()}}}let It=[],R=[];function ie(w){if(w.samples>0){if(Ot(w)===!1){let v=w.textures,N=w.width,Y=w.height,Q=n.COLOR_BUFFER_BIT,q=w.stencilBuffer?n.DEPTH_STENCIL_ATTACHMENT:n.DEPTH_ATTACHMENT,vt=i.get(w),st=v.length>1;if(st)for(let dt=0;dt<v.length;dt++)e.bindFramebuffer(n.FRAMEBUFFER,vt.__webglMultisampledFramebuffer),n.framebufferRenderbuffer(n.FRAMEBUFFER,n.COLOR_ATTACHMENT0+dt,n.RENDERBUFFER,null),e.bindFramebuffer(n.FRAMEBUFFER,vt.__webglFramebuffer),n.framebufferTexture2D(n.DRAW_FRAMEBUFFER,n.COLOR_ATTACHMENT0+dt,n.TEXTURE_2D,null,0);e.bindFramebuffer(n.READ_FRAMEBUFFER,vt.__webglMultisampledFramebuffer),e.bindFramebuffer(n.DRAW_FRAMEBUFFER,vt.__webglFramebuffer);for(let dt=0;dt<v.length;dt++){if(w.resolveDepthBuffer&&(w.depthBuffer&&(Q|=n.DEPTH_BUFFER_BIT),w.stencilBuffer&&w.resolveStencilBuffer&&(Q|=n.STENCIL_BUFFER_BIT)),st){n.framebufferRenderbuffer(n.READ_FRAMEBUFFER,n.COLOR_ATTACHMENT0,n.RENDERBUFFER,vt.__webglColorRenderbuffer[dt]);let Ht=i.get(v[dt]).__webglTexture;n.framebufferTexture2D(n.DRAW_FRAMEBUFFER,n.COLOR_ATTACHMENT0,n.TEXTURE_2D,Ht,0)}n.blitFramebuffer(0,0,N,Y,0,0,N,Y,Q,n.NEAREST),l===!0&&(It.length=0,R.length=0,It.push(n.COLOR_ATTACHMENT0+dt),w.depthBuffer&&w.resolveDepthBuffer===!1&&(It.push(q),R.push(q),n.invalidateFramebuffer(n.DRAW_FRAMEBUFFER,R)),n.invalidateFramebuffer(n.READ_FRAMEBUFFER,It))}if(e.bindFramebuffer(n.READ_FRAMEBUFFER,null),e.bindFramebuffer(n.DRAW_FRAMEBUFFER,null),st)for(let dt=0;dt<v.length;dt++){e.bindFramebuffer(n.FRAMEBUFFER,vt.__webglMultisampledFramebuffer),n.framebufferRenderbuffer(n.FRAMEBUFFER,n.COLOR_ATTACHMENT0+dt,n.RENDERBUFFER,vt.__webglColorRenderbuffer[dt]);let Ht=i.get(v[dt]).__webglTexture;e.bindFramebuffer(n.FRAMEBUFFER,vt.__webglFramebuffer),n.framebufferTexture2D(n.DRAW_FRAMEBUFFER,n.COLOR_ATTACHMENT0+dt,n.TEXTURE_2D,Ht,0)}e.bindFramebuffer(n.DRAW_FRAMEBUFFER,vt.__webglMultisampledFramebuffer)}else if(w.depthBuffer&&w.resolveDepthBuffer===!1&&l){let v=w.stencilBuffer?n.DEPTH_STENCIL_ATTACHMENT:n.DEPTH_ATTACHMENT;n.invalidateFramebuffer(n.DRAW_FRAMEBUFFER,[v])}}}function Ut(w){return Math.min(s.maxSamples,w.samples)}function Ot(w){let v=i.get(w);return w.samples>0&&t.has("WEBGL_multisampled_render_to_texture")===!0&&v.__useRenderToTexture!==!1}function wt(w){let v=o.render.frame;h.get(w)!==v&&(h.set(w,v),w.update())}function $t(w,v){let N=w.colorSpace,Y=w.format,Q=w.type;return w.isCompressedTexture===!0||w.isVideoTexture===!0||N!==$n&&N!==Hn&&(Yt.getTransfer(N)===te?(Y!==je||Q!==bn)&&console.warn("THREE.WebGLTextures: sRGB encoded textures have to use RGBAFormat and UnsignedByteType."):console.error("THREE.WebGLTextures: Unsupported texture color space:",N)),v}function Tt(w){return typeof HTMLImageElement!="undefined"&&w instanceof HTMLImageElement?(c.width=w.naturalWidth||w.width,c.height=w.naturalHeight||w.height):typeof VideoFrame!="undefined"&&w instanceof VideoFrame?(c.width=w.displayWidth,c.height=w.displayHeight):(c.width=w.width,c.height=w.height),c}this.allocateTextureUnit=V,this.resetTextureUnits=y,this.setTexture2D=H,this.setTexture2DArray=$,this.setTexture3D=k,this.setTextureCube=Z,this.rebindTextures=et,this.setupRenderTarget=at,this.updateRenderTargetMipmap=Et,this.updateMultisampleRenderTarget=ie,this.setupDepthRenderbuffer=bt,this.setupFrameBufferTexture=J,this.useMultisampledRTT=Ot}function mv(n,t){function e(i,s=Hn){let r,o=Yt.getTransfer(s);if(i===bn)return n.UNSIGNED_BYTE;if(i===Sc)return n.UNSIGNED_SHORT_4_4_4_4;if(i===bc)return n.UNSIGNED_SHORT_5_5_5_1;if(i===zf)return n.UNSIGNED_INT_5_9_9_9_REV;if(i===Bf)return n.BYTE;if(i===Vf)return n.SHORT;if(i===zs)return n.UNSIGNED_SHORT;if(i===Mc)return n.INT;if(i===pi)return n.UNSIGNED_INT;if(i===Mn)return n.FLOAT;if(i===Xs)return n.HALF_FLOAT;if(i===kf)return n.ALPHA;if(i===Hf)return n.RGB;if(i===je)return n.RGBA;if(i===Gf)return n.LUMINANCE;if(i===Wf)return n.LUMINANCE_ALPHA;if(i===rs)return n.DEPTH_COMPONENT;if(i===fs)return n.DEPTH_STENCIL;if(i===Xf)return n.RED;if(i===Ec)return n.RED_INTEGER;if(i===qf)return n.RG;if(i===wc)return n.RG_INTEGER;if(i===Tc)return n.RGBA_INTEGER;if(i===mo||i===go||i===xo||i===_o)if(o===te)if(r=t.get("WEBGL_compressed_texture_s3tc_srgb"),r!==null){if(i===mo)return r.COMPRESSED_SRGB_S3TC_DXT1_EXT;if(i===go)return r.COMPRESSED_SRGB_ALPHA_S3TC_DXT1_EXT;if(i===xo)return r.COMPRESSED_SRGB_ALPHA_S3TC_DXT3_EXT;if(i===_o)return r.COMPRESSED_SRGB_ALPHA_S3TC_DXT5_EXT}else return null;else if(r=t.get("WEBGL_compressed_texture_s3tc"),r!==null){if(i===mo)return r.COMPRESSED_RGB_S3TC_DXT1_EXT;if(i===go)return r.COMPRESSED_RGBA_S3TC_DXT1_EXT;if(i===xo)return r.COMPRESSED_RGBA_S3TC_DXT3_EXT;if(i===_o)return r.COMPRESSED_RGBA_S3TC_DXT5_EXT}else return null;if(i===xl||i===_l||i===vl||i===yl)if(r=t.get("WEBGL_compressed_texture_pvrtc"),r!==null){if(i===xl)return r.COMPRESSED_RGB_PVRTC_4BPPV1_IMG;if(i===_l)return r.COMPRESSED_RGB_PVRTC_2BPPV1_IMG;if(i===vl)return r.COMPRESSED_RGBA_PVRTC_4BPPV1_IMG;if(i===yl)return r.COMPRESSED_RGBA_PVRTC_2BPPV1_IMG}else return null;if(i===Ml||i===Sl||i===bl)if(r=t.get("WEBGL_compressed_texture_etc"),r!==null){if(i===Ml||i===Sl)return o===te?r.COMPRESSED_SRGB8_ETC2:r.COMPRESSED_RGB8_ETC2;if(i===bl)return o===te?r.COMPRESSED_SRGB8_ALPHA8_ETC2_EAC:r.COMPRESSED_RGBA8_ETC2_EAC}else return null;if(i===El||i===wl||i===Tl||i===Al||i===Cl||i===Rl||i===Pl||i===Il||i===Ll||i===Dl||i===Ul||i===Nl||i===Fl||i===Ol)if(r=t.get("WEBGL_compressed_texture_astc"),r!==null){if(i===El)return o===te?r.COMPRESSED_SRGB8_ALPHA8_ASTC_4x4_KHR:r.COMPRESSED_RGBA_ASTC_4x4_KHR;if(i===wl)return o===te?r.COMPRESSED_SRGB8_ALPHA8_ASTC_5x4_KHR:r.COMPRESSED_RGBA_ASTC_5x4_KHR;if(i===Tl)return o===te?r.COMPRESSED_SRGB8_ALPHA8_ASTC_5x5_KHR:r.COMPRESSED_RGBA_ASTC_5x5_KHR;if(i===Al)return o===te?r.COMPRESSED_SRGB8_ALPHA8_ASTC_6x5_KHR:r.COMPRESSED_RGBA_ASTC_6x5_KHR;if(i===Cl)return o===te?r.COMPRESSED_SRGB8_ALPHA8_ASTC_6x6_KHR:r.COMPRESSED_RGBA_ASTC_6x6_KHR;if(i===Rl)return o===te?r.COMPRESSED_SRGB8_ALPHA8_ASTC_8x5_KHR:r.COMPRESSED_RGBA_ASTC_8x5_KHR;if(i===Pl)return o===te?r.COMPRESSED_SRGB8_ALPHA8_ASTC_8x6_KHR:r.COMPRESSED_RGBA_ASTC_8x6_KHR;if(i===Il)return o===te?r.COMPRESSED_SRGB8_ALPHA8_ASTC_8x8_KHR:r.COMPRESSED_RGBA_ASTC_8x8_KHR;if(i===Ll)return o===te?r.COMPRESSED_SRGB8_ALPHA8_ASTC_10x5_KHR:r.COMPRESSED_RGBA_ASTC_10x5_KHR;if(i===Dl)return o===te?r.COMPRESSED_SRGB8_ALPHA8_ASTC_10x6_KHR:r.COMPRESSED_RGBA_ASTC_10x6_KHR;if(i===Ul)return o===te?r.COMPRESSED_SRGB8_ALPHA8_ASTC_10x8_KHR:r.COMPRESSED_RGBA_ASTC_10x8_KHR;if(i===Nl)return o===te?r.COMPRESSED_SRGB8_ALPHA8_ASTC_10x10_KHR:r.COMPRESSED_RGBA_ASTC_10x10_KHR;if(i===Fl)return o===te?r.COMPRESSED_SRGB8_ALPHA8_ASTC_12x10_KHR:r.COMPRESSED_RGBA_ASTC_12x10_KHR;if(i===Ol)return o===te?r.COMPRESSED_SRGB8_ALPHA8_ASTC_12x12_KHR:r.COMPRESSED_RGBA_ASTC_12x12_KHR}else return null;if(i===vo||i===Bl||i===Vl)if(r=t.get("EXT_texture_compression_bptc"),r!==null){if(i===vo)return o===te?r.COMPRESSED_SRGB_ALPHA_BPTC_UNORM_EXT:r.COMPRESSED_RGBA_BPTC_UNORM_EXT;if(i===Bl)return r.COMPRESSED_RGB_BPTC_SIGNED_FLOAT_EXT;if(i===Vl)return r.COMPRESSED_RGB_BPTC_UNSIGNED_FLOAT_EXT}else return null;if(i===Yf||i===zl||i===kl||i===Hl)if(r=t.get("EXT_texture_compression_rgtc"),r!==null){if(i===vo)return r.COMPRESSED_RED_RGTC1_EXT;if(i===zl)return r.COMPRESSED_SIGNED_RED_RGTC1_EXT;if(i===kl)return r.COMPRESSED_RED_GREEN_RGTC2_EXT;if(i===Hl)return r.COMPRESSED_SIGNED_RED_GREEN_RGTC2_EXT}else return null;return i===us?n.UNSIGNED_INT_24_8:n[i]!==void 0?n[i]:null}return{convert:e}}var rc=class extends Me{constructor(t=[]){super(),this.isArrayCamera=!0,this.cameras=t}},is=class extends Oe{constructor(){super(),this.isGroup=!0,this.type="Group"}},gv={type:"move"},Vs=class{constructor(){this._targetRay=null,this._grip=null,this._hand=null}getHandSpace(){return this._hand===null&&(this._hand=new is,this._hand.matrixAutoUpdate=!1,this._hand.visible=!1,this._hand.joints={},this._hand.inputState={pinching:!1}),this._hand}getTargetRaySpace(){return this._targetRay===null&&(this._targetRay=new is,this._targetRay.matrixAutoUpdate=!1,this._targetRay.visible=!1,this._targetRay.hasLinearVelocity=!1,this._targetRay.linearVelocity=new F,this._targetRay.hasAngularVelocity=!1,this._targetRay.angularVelocity=new F),this._targetRay}getGripSpace(){return this._grip===null&&(this._grip=new is,this._grip.matrixAutoUpdate=!1,this._grip.visible=!1,this._grip.hasLinearVelocity=!1,this._grip.linearVelocity=new F,this._grip.hasAngularVelocity=!1,this._grip.angularVelocity=new F),this._grip}dispatchEvent(t){return this._targetRay!==null&&this._targetRay.dispatchEvent(t),this._grip!==null&&this._grip.dispatchEvent(t),this._hand!==null&&this._hand.dispatchEvent(t),this}connect(t){if(t&&t.hand){let e=this._hand;if(e)for(let i of t.hand.values())this._getHandJoint(e,i)}return this.dispatchEvent({type:"connected",data:t}),this}disconnect(t){return this.dispatchEvent({type:"disconnected",data:t}),this._targetRay!==null&&(this._targetRay.visible=!1),this._grip!==null&&(this._grip.visible=!1),this._hand!==null&&(this._hand.visible=!1),this}update(t,e,i){let s=null,r=null,o=null,a=this._targetRay,l=this._grip,c=this._hand;if(t&&e.session.visibilityState!=="visible-blurred"){if(c&&t.hand){o=!0;for(let _ of t.hand.values()){let u=e.getJointPose(_,i),f=this._getHandJoint(c,_);u!==null&&(f.matrix.fromArray(u.transform.matrix),f.matrix.decompose(f.position,f.rotation,f.scale),f.matrixWorldNeedsUpdate=!0,f.jointRadius=u.radius),f.visible=u!==null}let h=c.joints["index-finger-tip"],d=c.joints["thumb-tip"],p=h.position.distanceTo(d.position),m=.02,g=.005;c.inputState.pinching&&p>m+g?(c.inputState.pinching=!1,this.dispatchEvent({type:"pinchend",handedness:t.handedness,target:this})):!c.inputState.pinching&&p<=m-g&&(c.inputState.pinching=!0,this.dispatchEvent({type:"pinchstart",handedness:t.handedness,target:this}))}else l!==null&&t.gripSpace&&(r=e.getPose(t.gripSpace,i),r!==null&&(l.matrix.fromArray(r.transform.matrix),l.matrix.decompose(l.position,l.rotation,l.scale),l.matrixWorldNeedsUpdate=!0,r.linearVelocity?(l.hasLinearVelocity=!0,l.linearVelocity.copy(r.linearVelocity)):l.hasLinearVelocity=!1,r.angularVelocity?(l.hasAngularVelocity=!0,l.angularVelocity.copy(r.angularVelocity)):l.hasAngularVelocity=!1));a!==null&&(s=e.getPose(t.targetRaySpace,i),s===null&&r!==null&&(s=r),s!==null&&(a.matrix.fromArray(s.transform.matrix),a.matrix.decompose(a.position,a.rotation,a.scale),a.matrixWorldNeedsUpdate=!0,s.linearVelocity?(a.hasLinearVelocity=!0,a.linearVelocity.copy(s.linearVelocity)):a.hasLinearVelocity=!1,s.angularVelocity?(a.hasAngularVelocity=!0,a.angularVelocity.copy(s.angularVelocity)):a.hasAngularVelocity=!1,this.dispatchEvent(gv)))}return a!==null&&(a.visible=s!==null),l!==null&&(l.visible=r!==null),c!==null&&(c.visible=o!==null),this}_getHandJoint(t,e){if(t.joints[e.jointName]===void 0){let i=new is;i.matrixAutoUpdate=!1,i.visible=!1,t.joints[e.jointName]=i,t.add(i)}return t.joints[e.jointName]}},xv=`
void main() {

	gl_Position = vec4( position, 1.0 );

}`,_v=`
uniform sampler2DArray depthColor;
uniform float depthWidth;
uniform float depthHeight;

void main() {

	vec2 coord = vec2( gl_FragCoord.x / depthWidth, gl_FragCoord.y / depthHeight );

	if ( coord.x >= 1.0 ) {

		gl_FragDepth = texture( depthColor, vec3( coord.x - 1.0, coord.y, 1 ) ).r;

	} else {

		gl_FragDepth = texture( depthColor, vec3( coord.x, coord.y, 0 ) ).r;

	}

}`,oc=class{constructor(){this.texture=null,this.mesh=null,this.depthNear=0,this.depthFar=0}init(t,e,i){if(this.texture===null){let s=new Xe,r=t.properties.get(s);r.__webglTexture=e.texture,(e.depthNear!=i.depthNear||e.depthFar!=i.depthFar)&&(this.depthNear=e.depthNear,this.depthFar=e.depthFar),this.texture=s}}getMesh(t){if(this.texture!==null&&this.mesh===null){let e=t.cameras[0].viewport,i=new cn({vertexShader:xv,fragmentShader:_v,uniforms:{depthColor:{value:this.texture},depthWidth:{value:e.z},depthHeight:{value:e.w}}});this.mesh=new Fe(new Fo(20,20),i)}return this.mesh}reset(){this.texture=null,this.mesh=null}getDepthTexture(){return this.texture}},ac=class extends qn{constructor(t,e){super();let i=this,s=null,r=1,o=null,a="local-floor",l=1,c=null,h=null,d=null,p=null,m=null,g=null,_=new oc,u=e.getContextAttributes(),f=null,b=null,S=[],E=[],C=new Zt,A=null,T=new Me;T.layers.enable(1),T.viewport=new ae;let P=new Me;P.layers.enable(2),P.viewport=new ae;let G=[T,P],x=new rc;x.layers.enable(1),x.layers.enable(2);let y=null,V=null;this.cameraAutoUpdate=!0,this.enabled=!1,this.isPresenting=!1,this.getController=function(X){let J=S[X];return J===void 0&&(J=new Vs,S[X]=J),J.getTargetRaySpace()},this.getControllerGrip=function(X){let J=S[X];return J===void 0&&(J=new Vs,S[X]=J),J.getGripSpace()},this.getHand=function(X){let J=S[X];return J===void 0&&(J=new Vs,S[X]=J),J.getHandSpace()};function U(X){let J=E.indexOf(X.inputSource);if(J===-1)return;let ht=S[J];ht!==void 0&&(ht.update(X.inputSource,X.frame,c||o),ht.dispatchEvent({type:X.type,data:X.inputSource}))}function H(){s.removeEventListener("select",U),s.removeEventListener("selectstart",U),s.removeEventListener("selectend",U),s.removeEventListener("squeeze",U),s.removeEventListener("squeezestart",U),s.removeEventListener("squeezeend",U),s.removeEventListener("end",H),s.removeEventListener("inputsourceschange",$);for(let X=0;X<S.length;X++){let J=E[X];J!==null&&(E[X]=null,S[X].disconnect(J))}y=null,V=null,_.reset(),t.setRenderTarget(f),m=null,p=null,d=null,s=null,b=null,Vt.stop(),i.isPresenting=!1,t.setPixelRatio(A),t.setSize(C.width,C.height,!1),i.dispatchEvent({type:"sessionend"})}this.setFramebufferScaleFactor=function(X){r=X,i.isPresenting===!0&&console.warn("THREE.WebXRManager: Cannot change framebuffer scale while presenting.")},this.setReferenceSpaceType=function(X){a=X,i.isPresenting===!0&&console.warn("THREE.WebXRManager: Cannot change reference space type while presenting.")},this.getReferenceSpace=function(){return c||o},this.setReferenceSpace=function(X){c=X},this.getBaseLayer=function(){return p!==null?p:m},this.getBinding=function(){return d},this.getFrame=function(){return g},this.getSession=function(){return s},this.setSession=async function(X){if(s=X,s!==null){if(f=t.getRenderTarget(),s.addEventListener("select",U),s.addEventListener("selectstart",U),s.addEventListener("selectend",U),s.addEventListener("squeeze",U),s.addEventListener("squeezestart",U),s.addEventListener("squeezeend",U),s.addEventListener("end",H),s.addEventListener("inputsourceschange",$),u.xrCompatible!==!0&&await e.makeXRCompatible(),A=t.getPixelRatio(),t.getSize(C),s.renderState.layers===void 0){let J={antialias:u.antialias,alpha:!0,depth:u.depth,stencil:u.stencil,framebufferScaleFactor:r};m=new XRWebGLLayer(s,e,J),s.updateRenderState({baseLayer:m}),t.setPixelRatio(1),t.setSize(m.framebufferWidth,m.framebufferHeight,!1),b=new En(m.framebufferWidth,m.framebufferHeight,{format:je,type:bn,colorSpace:t.outputColorSpace,stencilBuffer:u.stencil})}else{let J=null,ht=null,rt=null;u.depth&&(rt=u.stencil?e.DEPTH24_STENCIL8:e.DEPTH_COMPONENT24,J=u.stencil?fs:rs,ht=u.stencil?us:pi);let bt={colorFormat:e.RGBA8,depthFormat:rt,scaleFactor:r};d=new XRWebGLBinding(s,e),p=d.createProjectionLayer(bt),s.updateRenderState({layers:[p]}),t.setPixelRatio(1),t.setSize(p.textureWidth,p.textureHeight,!1),b=new En(p.textureWidth,p.textureHeight,{format:je,type:bn,depthTexture:new Bo(p.textureWidth,p.textureHeight,ht,void 0,void 0,void 0,void 0,void 0,void 0,J),stencilBuffer:u.stencil,colorSpace:t.outputColorSpace,samples:u.antialias?4:0,resolveDepthBuffer:p.ignoreDepthValues===!1})}b.isXRRenderTarget=!0,this.setFoveation(l),c=null,o=await s.requestReferenceSpace(a),Vt.setContext(s),Vt.start(),i.isPresenting=!0,i.dispatchEvent({type:"sessionstart"})}},this.getEnvironmentBlendMode=function(){if(s!==null)return s.environmentBlendMode},this.getDepthTexture=function(){return _.getDepthTexture()};function $(X){for(let J=0;J<X.removed.length;J++){let ht=X.removed[J],rt=E.indexOf(ht);rt>=0&&(E[rt]=null,S[rt].disconnect(ht))}for(let J=0;J<X.added.length;J++){let ht=X.added[J],rt=E.indexOf(ht);if(rt===-1){for(let et=0;et<S.length;et++)if(et>=E.length){E.push(ht),rt=et;break}else if(E[et]===null){E[et]=ht,rt=et;break}if(rt===-1)break}let bt=S[rt];bt&&bt.connect(ht)}}let k=new F,Z=new F;function z(X,J,ht){k.setFromMatrixPosition(J.matrixWorld),Z.setFromMatrixPosition(ht.matrixWorld);let rt=k.distanceTo(Z),bt=J.projectionMatrix.elements,et=ht.projectionMatrix.elements,at=bt[14]/(bt[10]-1),Et=bt[14]/(bt[10]+1),It=(bt[9]+1)/bt[5],R=(bt[9]-1)/bt[5],ie=(bt[8]-1)/bt[0],Ut=(et[8]+1)/et[0],Ot=at*ie,wt=at*Ut,$t=rt/(-ie+Ut),Tt=$t*-ie;if(J.matrixWorld.decompose(X.position,X.quaternion,X.scale),X.translateX(Tt),X.translateZ($t),X.matrixWorld.compose(X.position,X.quaternion,X.scale),X.matrixWorldInverse.copy(X.matrixWorld).invert(),bt[10]===-1)X.projectionMatrix.copy(J.projectionMatrix),X.projectionMatrixInverse.copy(J.projectionMatrixInverse);else{let w=at+$t,v=Et+$t,N=Ot-Tt,Y=wt+(rt-Tt),Q=It*Et/v*w,q=R*Et/v*w;X.projectionMatrix.makePerspective(N,Y,Q,q,w,v),X.projectionMatrixInverse.copy(X.projectionMatrix).invert()}}function it(X,J){J===null?X.matrixWorld.copy(X.matrix):X.matrixWorld.multiplyMatrices(J.matrixWorld,X.matrix),X.matrixWorldInverse.copy(X.matrixWorld).invert()}this.updateCamera=function(X){if(s===null)return;let J=X.near,ht=X.far;_.texture!==null&&(_.depthNear>0&&(J=_.depthNear),_.depthFar>0&&(ht=_.depthFar)),x.near=P.near=T.near=J,x.far=P.far=T.far=ht,(y!==x.near||V!==x.far)&&(s.updateRenderState({depthNear:x.near,depthFar:x.far}),y=x.near,V=x.far);let rt=X.parent,bt=x.cameras;it(x,rt);for(let et=0;et<bt.length;et++)it(bt[et],rt);bt.length===2?z(x,T,P):x.projectionMatrix.copy(T.projectionMatrix),ot(X,x,rt)};function ot(X,J,ht){ht===null?X.matrix.copy(J.matrixWorld):(X.matrix.copy(ht.matrixWorld),X.matrix.invert(),X.matrix.multiply(J.matrixWorld)),X.matrix.decompose(X.position,X.quaternion,X.scale),X.updateMatrixWorld(!0),X.projectionMatrix.copy(J.projectionMatrix),X.projectionMatrixInverse.copy(J.projectionMatrixInverse),X.isPerspectiveCamera&&(X.fov=Wl*2*Math.atan(1/X.projectionMatrix.elements[5]),X.zoom=1)}this.getCamera=function(){return x},this.getFoveation=function(){if(!(p===null&&m===null))return l},this.setFoveation=function(X){l=X,p!==null&&(p.fixedFoveation=X),m!==null&&m.fixedFoveation!==void 0&&(m.fixedFoveation=X)},this.hasDepthSensing=function(){return _.texture!==null},this.getDepthSensingMesh=function(){return _.getMesh(x)};let pt=null;function Bt(X,J){if(h=J.getViewerPose(c||o),g=J,h!==null){let ht=h.views;m!==null&&(t.setRenderTargetFramebuffer(b,m.framebuffer),t.setRenderTarget(b));let rt=!1;ht.length!==x.cameras.length&&(x.cameras.length=0,rt=!0);for(let et=0;et<ht.length;et++){let at=ht[et],Et=null;if(m!==null)Et=m.getViewport(at);else{let R=d.getViewSubImage(p,at);Et=R.viewport,et===0&&(t.setRenderTargetTextures(b,R.colorTexture,p.ignoreDepthValues?void 0:R.depthStencilTexture),t.setRenderTarget(b))}let It=G[et];It===void 0&&(It=new Me,It.layers.enable(et),It.viewport=new ae,G[et]=It),It.matrix.fromArray(at.transform.matrix),It.matrix.decompose(It.position,It.quaternion,It.scale),It.projectionMatrix.fromArray(at.projectionMatrix),It.projectionMatrixInverse.copy(It.projectionMatrix).invert(),It.viewport.set(Et.x,Et.y,Et.width,Et.height),et===0&&(x.matrix.copy(It.matrix),x.matrix.decompose(x.position,x.quaternion,x.scale)),rt===!0&&x.cameras.push(It)}let bt=s.enabledFeatures;if(bt&&bt.includes("depth-sensing")){let et=d.getDepthInformation(ht[0]);et&&et.isValid&&et.texture&&_.init(t,et,s.renderState)}}for(let ht=0;ht<S.length;ht++){let rt=E[ht],bt=S[ht];rt!==null&&bt!==void 0&&bt.update(rt,J,c||o)}pt&&pt(X,J),J.detectedPlanes&&i.dispatchEvent({type:"planesdetected",data:J}),g=null}let Vt=new Qf;Vt.setAnimationLoop(Bt),this.setAnimationLoop=function(X){pt=X},this.dispose=function(){}}},ai=new wn,vv=new le;function yv(n,t){function e(u,f){u.matrixAutoUpdate===!0&&u.updateMatrix(),f.value.copy(u.matrix)}function i(u,f){f.color.getRGB(u.fogColor.value,Jf(n)),f.isFog?(u.fogNear.value=f.near,u.fogFar.value=f.far):f.isFogExp2&&(u.fogDensity.value=f.density)}function s(u,f,b,S,E){f.isMeshBasicMaterial||f.isMeshLambertMaterial?r(u,f):f.isMeshToonMaterial?(r(u,f),d(u,f)):f.isMeshPhongMaterial?(r(u,f),h(u,f)):f.isMeshStandardMaterial?(r(u,f),p(u,f),f.isMeshPhysicalMaterial&&m(u,f,E)):f.isMeshMatcapMaterial?(r(u,f),g(u,f)):f.isMeshDepthMaterial?r(u,f):f.isMeshDistanceMaterial?(r(u,f),_(u,f)):f.isMeshNormalMaterial?r(u,f):f.isLineBasicMaterial?(o(u,f),f.isLineDashedMaterial&&a(u,f)):f.isPointsMaterial?l(u,f,b,S):f.isSpriteMaterial?c(u,f):f.isShadowMaterial?(u.color.value.copy(f.color),u.opacity.value=f.opacity):f.isShaderMaterial&&(f.uniformsNeedUpdate=!1)}function r(u,f){u.opacity.value=f.opacity,f.color&&u.diffuse.value.copy(f.color),f.emissive&&u.emissive.value.copy(f.emissive).multiplyScalar(f.emissiveIntensity),f.map&&(u.map.value=f.map,e(f.map,u.mapTransform)),f.alphaMap&&(u.alphaMap.value=f.alphaMap,e(f.alphaMap,u.alphaMapTransform)),f.bumpMap&&(u.bumpMap.value=f.bumpMap,e(f.bumpMap,u.bumpMapTransform),u.bumpScale.value=f.bumpScale,f.side===Re&&(u.bumpScale.value*=-1)),f.normalMap&&(u.normalMap.value=f.normalMap,e(f.normalMap,u.normalMapTransform),u.normalScale.value.copy(f.normalScale),f.side===Re&&u.normalScale.value.negate()),f.displacementMap&&(u.displacementMap.value=f.displacementMap,e(f.displacementMap,u.displacementMapTransform),u.displacementScale.value=f.displacementScale,u.displacementBias.value=f.displacementBias),f.emissiveMap&&(u.emissiveMap.value=f.emissiveMap,e(f.emissiveMap,u.emissiveMapTransform)),f.specularMap&&(u.specularMap.value=f.specularMap,e(f.specularMap,u.specularMapTransform)),f.alphaTest>0&&(u.alphaTest.value=f.alphaTest);let b=t.get(f),S=b.envMap,E=b.envMapRotation;S&&(u.envMap.value=S,ai.copy(E),ai.x*=-1,ai.y*=-1,ai.z*=-1,S.isCubeTexture&&S.isRenderTargetTexture===!1&&(ai.y*=-1,ai.z*=-1),u.envMapRotation.value.setFromMatrix4(vv.makeRotationFromEuler(ai)),u.flipEnvMap.value=S.isCubeTexture&&S.isRenderTargetTexture===!1?-1:1,u.reflectivity.value=f.reflectivity,u.ior.value=f.ior,u.refractionRatio.value=f.refractionRatio),f.lightMap&&(u.lightMap.value=f.lightMap,u.lightMapIntensity.value=f.lightMapIntensity,e(f.lightMap,u.lightMapTransform)),f.aoMap&&(u.aoMap.value=f.aoMap,u.aoMapIntensity.value=f.aoMapIntensity,e(f.aoMap,u.aoMapTransform))}function o(u,f){u.diffuse.value.copy(f.color),u.opacity.value=f.opacity,f.map&&(u.map.value=f.map,e(f.map,u.mapTransform))}function a(u,f){u.dashSize.value=f.dashSize,u.totalSize.value=f.dashSize+f.gapSize,u.scale.value=f.scale}function l(u,f,b,S){u.diffuse.value.copy(f.color),u.opacity.value=f.opacity,u.size.value=f.size*b,u.scale.value=S*.5,f.map&&(u.map.value=f.map,e(f.map,u.uvTransform)),f.alphaMap&&(u.alphaMap.value=f.alphaMap,e(f.alphaMap,u.alphaMapTransform)),f.alphaTest>0&&(u.alphaTest.value=f.alphaTest)}function c(u,f){u.diffuse.value.copy(f.color),u.opacity.value=f.opacity,u.rotation.value=f.rotation,f.map&&(u.map.value=f.map,e(f.map,u.mapTransform)),f.alphaMap&&(u.alphaMap.value=f.alphaMap,e(f.alphaMap,u.alphaMapTransform)),f.alphaTest>0&&(u.alphaTest.value=f.alphaTest)}function h(u,f){u.specular.value.copy(f.specular),u.shininess.value=Math.max(f.shininess,1e-4)}function d(u,f){f.gradientMap&&(u.gradientMap.value=f.gradientMap)}function p(u,f){u.metalness.value=f.metalness,f.metalnessMap&&(u.metalnessMap.value=f.metalnessMap,e(f.metalnessMap,u.metalnessMapTransform)),u.roughness.value=f.roughness,f.roughnessMap&&(u.roughnessMap.value=f.roughnessMap,e(f.roughnessMap,u.roughnessMapTransform)),f.envMap&&(u.envMapIntensity.value=f.envMapIntensity)}function m(u,f,b){u.ior.value=f.ior,f.sheen>0&&(u.sheenColor.value.copy(f.sheenColor).multiplyScalar(f.sheen),u.sheenRoughness.value=f.sheenRoughness,f.sheenColorMap&&(u.sheenColorMap.value=f.sheenColorMap,e(f.sheenColorMap,u.sheenColorMapTransform)),f.sheenRoughnessMap&&(u.sheenRoughnessMap.value=f.sheenRoughnessMap,e(f.sheenRoughnessMap,u.sheenRoughnessMapTransform))),f.clearcoat>0&&(u.clearcoat.value=f.clearcoat,u.clearcoatRoughness.value=f.clearcoatRoughness,f.clearcoatMap&&(u.clearcoatMap.value=f.clearcoatMap,e(f.clearcoatMap,u.clearcoatMapTransform)),f.clearcoatRoughnessMap&&(u.clearcoatRoughnessMap.value=f.clearcoatRoughnessMap,e(f.clearcoatRoughnessMap,u.clearcoatRoughnessMapTransform)),f.clearcoatNormalMap&&(u.clearcoatNormalMap.value=f.clearcoatNormalMap,e(f.clearcoatNormalMap,u.clearcoatNormalMapTransform),u.clearcoatNormalScale.value.copy(f.clearcoatNormalScale),f.side===Re&&u.clearcoatNormalScale.value.negate())),f.dispersion>0&&(u.dispersion.value=f.dispersion),f.iridescence>0&&(u.iridescence.value=f.iridescence,u.iridescenceIOR.value=f.iridescenceIOR,u.iridescenceThicknessMinimum.value=f.iridescenceThicknessRange[0],u.iridescenceThicknessMaximum.value=f.iridescenceThicknessRange[1],f.iridescenceMap&&(u.iridescenceMap.value=f.iridescenceMap,e(f.iridescenceMap,u.iridescenceMapTransform)),f.iridescenceThicknessMap&&(u.iridescenceThicknessMap.value=f.iridescenceThicknessMap,e(f.iridescenceThicknessMap,u.iridescenceThicknessMapTransform))),f.transmission>0&&(u.transmission.value=f.transmission,u.transmissionSamplerMap.value=b.texture,u.transmissionSamplerSize.value.set(b.width,b.height),f.transmissionMap&&(u.transmissionMap.value=f.transmissionMap,e(f.transmissionMap,u.transmissionMapTransform)),u.thickness.value=f.thickness,f.thicknessMap&&(u.thicknessMap.value=f.thicknessMap,e(f.thicknessMap,u.thicknessMapTransform)),u.attenuationDistance.value=f.attenuationDistance,u.attenuationColor.value.copy(f.attenuationColor)),f.anisotropy>0&&(u.anisotropyVector.value.set(f.anisotropy*Math.cos(f.anisotropyRotation),f.anisotropy*Math.sin(f.anisotropyRotation)),f.anisotropyMap&&(u.anisotropyMap.value=f.anisotropyMap,e(f.anisotropyMap,u.anisotropyMapTransform))),u.specularIntensity.value=f.specularIntensity,u.specularColor.value.copy(f.specularColor),f.specularColorMap&&(u.specularColorMap.value=f.specularColorMap,e(f.specularColorMap,u.specularColorMapTransform)),f.specularIntensityMap&&(u.specularIntensityMap.value=f.specularIntensityMap,e(f.specularIntensityMap,u.specularIntensityMapTransform))}function g(u,f){f.matcap&&(u.matcap.value=f.matcap)}function _(u,f){let b=t.get(f).light;u.referencePosition.value.setFromMatrixPosition(b.matrixWorld),u.nearDistance.value=b.shadow.camera.near,u.farDistance.value=b.shadow.camera.far}return{refreshFogUniforms:i,refreshMaterialUniforms:s}}function Mv(n,t,e,i){let s={},r={},o=[],a=n.getParameter(n.MAX_UNIFORM_BUFFER_BINDINGS);function l(b,S){let E=S.program;i.uniformBlockBinding(b,E)}function c(b,S){let E=s[b.id];E===void 0&&(g(b),E=h(b),s[b.id]=E,b.addEventListener("dispose",u));let C=S.program;i.updateUBOMapping(b,C);let A=t.render.frame;r[b.id]!==A&&(p(b),r[b.id]=A)}function h(b){let S=d();b.__bindingPointIndex=S;let E=n.createBuffer(),C=b.__size,A=b.usage;return n.bindBuffer(n.UNIFORM_BUFFER,E),n.bufferData(n.UNIFORM_BUFFER,C,A),n.bindBuffer(n.UNIFORM_BUFFER,null),n.bindBufferBase(n.UNIFORM_BUFFER,S,E),E}function d(){for(let b=0;b<a;b++)if(o.indexOf(b)===-1)return o.push(b),b;return console.error("THREE.WebGLRenderer: Maximum number of simultaneously usable uniforms groups reached."),0}function p(b){let S=s[b.id],E=b.uniforms,C=b.__cache;n.bindBuffer(n.UNIFORM_BUFFER,S);for(let A=0,T=E.length;A<T;A++){let P=Array.isArray(E[A])?E[A]:[E[A]];for(let G=0,x=P.length;G<x;G++){let y=P[G];if(m(y,A,G,C)===!0){let V=y.__offset,U=Array.isArray(y.value)?y.value:[y.value],H=0;for(let $=0;$<U.length;$++){let k=U[$],Z=_(k);typeof k=="number"||typeof k=="boolean"?(y.__data[0]=k,n.bufferSubData(n.UNIFORM_BUFFER,V+H,y.__data)):k.isMatrix3?(y.__data[0]=k.elements[0],y.__data[1]=k.elements[1],y.__data[2]=k.elements[2],y.__data[3]=0,y.__data[4]=k.elements[3],y.__data[5]=k.elements[4],y.__data[6]=k.elements[5],y.__data[7]=0,y.__data[8]=k.elements[6],y.__data[9]=k.elements[7],y.__data[10]=k.elements[8],y.__data[11]=0):(k.toArray(y.__data,H),H+=Z.storage/Float32Array.BYTES_PER_ELEMENT)}n.bufferSubData(n.UNIFORM_BUFFER,V,y.__data)}}}n.bindBuffer(n.UNIFORM_BUFFER,null)}function m(b,S,E,C){let A=b.value,T=S+"_"+E;if(C[T]===void 0)return typeof A=="number"||typeof A=="boolean"?C[T]=A:C[T]=A.clone(),!0;{let P=C[T];if(typeof A=="number"||typeof A=="boolean"){if(P!==A)return C[T]=A,!0}else if(P.equals(A)===!1)return P.copy(A),!0}return!1}function g(b){let S=b.uniforms,E=0,C=16;for(let T=0,P=S.length;T<P;T++){let G=Array.isArray(S[T])?S[T]:[S[T]];for(let x=0,y=G.length;x<y;x++){let V=G[x],U=Array.isArray(V.value)?V.value:[V.value];for(let H=0,$=U.length;H<$;H++){let k=U[H],Z=_(k),z=E%C,it=z%Z.boundary,ot=z+it;E+=it,ot!==0&&C-ot<Z.storage&&(E+=C-ot),V.__data=new Float32Array(Z.storage/Float32Array.BYTES_PER_ELEMENT),V.__offset=E,E+=Z.storage}}}let A=E%C;return A>0&&(E+=C-A),b.__size=E,b.__cache={},this}function _(b){let S={boundary:0,storage:0};return typeof b=="number"||typeof b=="boolean"?(S.boundary=4,S.storage=4):b.isVector2?(S.boundary=8,S.storage=8):b.isVector3||b.isColor?(S.boundary=16,S.storage=12):b.isVector4?(S.boundary=16,S.storage=16):b.isMatrix3?(S.boundary=48,S.storage=48):b.isMatrix4?(S.boundary=64,S.storage=64):b.isTexture?console.warn("THREE.WebGLRenderer: Texture samplers can not be part of an uniforms group."):console.warn("THREE.WebGLRenderer: Unsupported uniform value type.",b),S}function u(b){let S=b.target;S.removeEventListener("dispose",u);let E=o.indexOf(S.__bindingPointIndex);o.splice(E,1),n.deleteBuffer(s[S.id]),delete s[S.id],delete r[S.id]}function f(){for(let b in s)n.deleteBuffer(s[b]);o=[],s={},r={}}return{bind:l,update:c,dispose:f}}var Vo=class{constructor(t={}){let{canvas:e=Em(),context:i=null,depth:s=!0,stencil:r=!1,alpha:o=!1,antialias:a=!1,premultipliedAlpha:l=!0,preserveDrawingBuffer:c=!1,powerPreference:h="default",failIfMajorPerformanceCaveat:d=!1}=t;this.isWebGLRenderer=!0;let p;if(i!==null){if(typeof WebGLRenderingContext!="undefined"&&i instanceof WebGLRenderingContext)throw new Error("THREE.WebGLRenderer: WebGL 1 is not supported since r163.");p=i.getContextAttributes().alpha}else p=o;let m=new Uint32Array(4),g=new Int32Array(4),_=null,u=null,f=[],b=[];this.domElement=e,this.debug={checkShaderErrors:!0,onShaderError:null},this.autoClear=!0,this.autoClearColor=!0,this.autoClearDepth=!0,this.autoClearStencil=!0,this.sortObjects=!0,this.clippingPlanes=[],this.localClippingEnabled=!1,this._outputColorSpace=an,this.toneMapping=Wn,this.toneMappingExposure=1;let S=this,E=!1,C=0,A=0,T=null,P=-1,G=null,x=new ae,y=new ae,V=null,U=new Gt(0),H=0,$=e.width,k=e.height,Z=1,z=null,it=null,ot=new ae(0,0,$,k),pt=new ae(0,0,$,k),Bt=!1,Vt=new No,X=!1,J=!1,ht=new le,rt=new le,bt=new F,et=new ae,at={background:null,fog:null,environment:null,overrideMaterial:null,isScene:!0},Et=!1;function It(){return T===null?Z:1}let R=i;function ie(M,L){return e.getContext(M,L)}try{let M={alpha:!0,depth:s,stencil:r,antialias:a,premultipliedAlpha:l,preserveDrawingBuffer:c,powerPreference:h,failIfMajorPerformanceCaveat:d};if("setAttribute"in e&&e.setAttribute("data-engine",`three.js r${yc}`),e.addEventListener("webglcontextlost",K,!1),e.addEventListener("webglcontextrestored",lt,!1),e.addEventListener("webglcontextcreationerror",ft,!1),R===null){let L="webgl2";if(R=ie(L,M),R===null)throw ie(L)?new Error("Error creating WebGL context with your selected attributes."):new Error("Error creating WebGL context.")}}catch(M){throw console.error("THREE.WebGLRenderer: "+M.message),M}let Ut,Ot,wt,$t,Tt,w,v,N,Y,Q,q,vt,st,dt,Ht,j,mt,Rt,Pt,gt,zt,Lt,Jt,I;function ut(){Ut=new Ox(R),Ut.init(),Lt=new mv(R,Ut),Ot=new Ix(R,Ut,t,Lt),wt=new fv(R),Ot.reverseDepthBuffer&&wt.buffers.depth.setReversed(!0),$t=new zx(R),Tt=new j_,w=new pv(R,Ut,wt,Tt,Ot,Lt,$t),v=new Dx(S),N=new Fx(S),Y=new Ym(R),Jt=new Rx(R,Y),Q=new Bx(R,Y,$t,Jt),q=new Hx(R,Q,Y,$t),Pt=new kx(R,Ot,w),j=new Lx(Tt),vt=new Q_(S,v,N,Ut,Ot,Jt,j),st=new yv(S,Tt),dt=new ev,Ht=new av(Ut),Rt=new Cx(S,v,N,wt,q,p,l),mt=new hv(S,q,Ot),I=new Mv(R,$t,Ot,wt),gt=new Px(R,Ut,$t),zt=new Vx(R,Ut,$t),$t.programs=vt.programs,S.capabilities=Ot,S.extensions=Ut,S.properties=Tt,S.renderLists=dt,S.shadowMap=mt,S.state=wt,S.info=$t}ut();let W=new ac(S,R);this.xr=W,this.getContext=function(){return R},this.getContextAttributes=function(){return R.getContextAttributes()},this.forceContextLoss=function(){let M=Ut.get("WEBGL_lose_context");M&&M.loseContext()},this.forceContextRestore=function(){let M=Ut.get("WEBGL_lose_context");M&&M.restoreContext()},this.getPixelRatio=function(){return Z},this.setPixelRatio=function(M){M!==void 0&&(Z=M,this.setSize($,k,!1))},this.getSize=function(M){return M.set($,k)},this.setSize=function(M,L,O=!0){if(W.isPresenting){console.warn("THREE.WebGLRenderer: Can't change size while VR device is presenting.");return}$=M,k=L,e.width=Math.floor(M*Z),e.height=Math.floor(L*Z),O===!0&&(e.style.width=M+"px",e.style.height=L+"px"),this.setViewport(0,0,M,L)},this.getDrawingBufferSize=function(M){return M.set($*Z,k*Z).floor()},this.setDrawingBufferSize=function(M,L,O){$=M,k=L,Z=O,e.width=Math.floor(M*O),e.height=Math.floor(L*O),this.setViewport(0,0,M,L)},this.getCurrentViewport=function(M){return M.copy(x)},this.getViewport=function(M){return M.copy(ot)},this.setViewport=function(M,L,O,B){M.isVector4?ot.set(M.x,M.y,M.z,M.w):ot.set(M,L,O,B),wt.viewport(x.copy(ot).multiplyScalar(Z).round())},this.getScissor=function(M){return M.copy(pt)},this.setScissor=function(M,L,O,B){M.isVector4?pt.set(M.x,M.y,M.z,M.w):pt.set(M,L,O,B),wt.scissor(y.copy(pt).multiplyScalar(Z).round())},this.getScissorTest=function(){return Bt},this.setScissorTest=function(M){wt.setScissorTest(Bt=M)},this.setOpaqueSort=function(M){z=M},this.setTransparentSort=function(M){it=M},this.getClearColor=function(M){return M.copy(Rt.getClearColor())},this.setClearColor=function(){Rt.setClearColor.apply(Rt,arguments)},this.getClearAlpha=function(){return Rt.getClearAlpha()},this.setClearAlpha=function(){Rt.setClearAlpha.apply(Rt,arguments)},this.clear=function(M=!0,L=!0,O=!0){let B=0;if(M){let D=!1;if(T!==null){let tt=T.texture.format;D=tt===Tc||tt===wc||tt===Ec}if(D){let tt=T.texture.type,ct=tt===bn||tt===pi||tt===zs||tt===us||tt===Sc||tt===bc,xt=Rt.getClearColor(),_t=Rt.getClearAlpha(),At=xt.r,Ct=xt.g,yt=xt.b;ct?(m[0]=At,m[1]=Ct,m[2]=yt,m[3]=_t,R.clearBufferuiv(R.COLOR,0,m)):(g[0]=At,g[1]=Ct,g[2]=yt,g[3]=_t,R.clearBufferiv(R.COLOR,0,g))}else B|=R.COLOR_BUFFER_BIT}L&&(B|=R.DEPTH_BUFFER_BIT,R.clearDepth(this.capabilities.reverseDepthBuffer?0:1)),O&&(B|=R.STENCIL_BUFFER_BIT,this.state.buffers.stencil.setMask(4294967295)),R.clear(B)},this.clearColor=function(){this.clear(!0,!1,!1)},this.clearDepth=function(){this.clear(!1,!0,!1)},this.clearStencil=function(){this.clear(!1,!1,!0)},this.dispose=function(){e.removeEventListener("webglcontextlost",K,!1),e.removeEventListener("webglcontextrestored",lt,!1),e.removeEventListener("webglcontextcreationerror",ft,!1),dt.dispose(),Ht.dispose(),Tt.dispose(),v.dispose(),N.dispose(),q.dispose(),Jt.dispose(),I.dispose(),vt.dispose(),W.dispose(),W.removeEventListener("sessionstart",Lc),W.removeEventListener("sessionend",Dc),Zn.stop()};function K(M){M.preventDefault(),console.log("THREE.WebGLRenderer: Context Lost."),E=!0}function lt(){console.log("THREE.WebGLRenderer: Context Restored."),E=!1;let M=$t.autoReset,L=mt.enabled,O=mt.autoUpdate,B=mt.needsUpdate,D=mt.type;ut(),$t.autoReset=M,mt.enabled=L,mt.autoUpdate=O,mt.needsUpdate=B,mt.type=D}function ft(M){console.error("THREE.WebGLRenderer: A WebGL context could not be created. Reason: ",M.statusMessage)}function kt(M){let L=M.target;L.removeEventListener("dispose",kt),ce(L)}function ce(M){be(M),Tt.remove(M)}function be(M){let L=Tt.get(M).programs;L!==void 0&&(L.forEach(function(O){vt.releaseProgram(O)}),M.isShaderMaterial&&vt.releaseShaderCache(M))}this.renderBufferDirect=function(M,L,O,B,D,tt){L===null&&(L=at);let ct=D.isMesh&&D.matrixWorld.determinant()<0,xt=cd(M,L,O,B,D);wt.setMaterial(B,ct);let _t=O.index,At=1;if(B.wireframe===!0){if(_t=Q.getWireframeAttribute(O),_t===void 0)return;At=2}let Ct=O.drawRange,yt=O.attributes.position,Kt=Ct.start*At,Qt=(Ct.start+Ct.count)*At;tt!==null&&(Kt=Math.max(Kt,tt.start*At),Qt=Math.min(Qt,(tt.start+tt.count)*At)),_t!==null?(Kt=Math.max(Kt,0),Qt=Math.min(Qt,_t.count)):yt!=null&&(Kt=Math.max(Kt,0),Qt=Math.min(Qt,yt.count));let re=Qt-Kt;if(re<0||re===1/0)return;Jt.setup(D,B,xt,O,_t);let Ie,Xt=gt;if(_t!==null&&(Ie=Y.get(_t),Xt=zt,Xt.setIndex(Ie)),D.isMesh)B.wireframe===!0?(wt.setLineWidth(B.wireframeLinewidth*It()),Xt.setMode(R.LINES)):Xt.setMode(R.TRIANGLES);else if(D.isLine){let Mt=B.linewidth;Mt===void 0&&(Mt=1),wt.setLineWidth(Mt*It()),D.isLineSegments?Xt.setMode(R.LINES):D.isLineLoop?Xt.setMode(R.LINE_LOOP):Xt.setMode(R.LINE_STRIP)}else D.isPoints?Xt.setMode(R.POINTS):D.isSprite&&Xt.setMode(R.TRIANGLES);if(D.isBatchedMesh)if(D._multiDrawInstances!==null)Xt.renderMultiDrawInstances(D._multiDrawStarts,D._multiDrawCounts,D._multiDrawCount,D._multiDrawInstances);else if(Ut.get("WEBGL_multi_draw"))Xt.renderMultiDraw(D._multiDrawStarts,D._multiDrawCounts,D._multiDrawCount);else{let Mt=D._multiDrawStarts,me=D._multiDrawCounts,qt=D._multiDrawCount,qe=_t?Y.get(_t).bytesPerElement:1,yi=Tt.get(B).currentProgram.getUniforms();for(let Le=0;Le<qt;Le++)yi.setValue(R,"_gl_DrawID",Le),Xt.render(Mt[Le]/qe,me[Le])}else if(D.isInstancedMesh)Xt.renderInstances(Kt,re,D.count);else if(O.isInstancedBufferGeometry){let Mt=O._maxInstanceCount!==void 0?O._maxInstanceCount:1/0,me=Math.min(O.instanceCount,Mt);Xt.renderInstances(Kt,re,me)}else Xt.render(Kt,re)};function Wt(M,L,O){M.transparent===!0&&M.side===Je&&M.forceSinglePass===!1?(M.side=Re,M.needsUpdate=!0,Ks(M,L,O),M.side=Xn,M.needsUpdate=!0,Ks(M,L,O),M.side=Je):Ks(M,L,O)}this.compile=function(M,L,O=null){O===null&&(O=M),u=Ht.get(O),u.init(L),b.push(u),O.traverseVisible(function(D){D.isLight&&D.layers.test(L.layers)&&(u.pushLight(D),D.castShadow&&u.pushShadow(D))}),M!==O&&M.traverseVisible(function(D){D.isLight&&D.layers.test(L.layers)&&(u.pushLight(D),D.castShadow&&u.pushShadow(D))}),u.setupLights();let B=new Set;return M.traverse(function(D){if(!(D.isMesh||D.isPoints||D.isLine||D.isSprite))return;let tt=D.material;if(tt)if(Array.isArray(tt))for(let ct=0;ct<tt.length;ct++){let xt=tt[ct];Wt(xt,O,D),B.add(xt)}else Wt(tt,O,D),B.add(tt)}),b.pop(),u=null,B},this.compileAsync=function(M,L,O=null){let B=this.compile(M,L,O);return new Promise(D=>{function tt(){if(B.forEach(function(ct){Tt.get(ct).currentProgram.isReady()&&B.delete(ct)}),B.size===0){D(M);return}setTimeout(tt,10)}Ut.get("KHR_parallel_shader_compile")!==null?tt():setTimeout(tt,10)})};let Ee=null;function hn(M){Ee&&Ee(M)}function Lc(){Zn.stop()}function Dc(){Zn.start()}let Zn=new Qf;Zn.setAnimationLoop(hn),typeof self!="undefined"&&Zn.setContext(self),this.setAnimationLoop=function(M){Ee=M,W.setAnimationLoop(M),M===null?Zn.stop():Zn.start()},W.addEventListener("sessionstart",Lc),W.addEventListener("sessionend",Dc),this.render=function(M,L){if(L!==void 0&&L.isCamera!==!0){console.error("THREE.WebGLRenderer.render: camera is not an instance of THREE.Camera.");return}if(E===!0)return;if(M.matrixWorldAutoUpdate===!0&&M.updateMatrixWorld(),L.parent===null&&L.matrixWorldAutoUpdate===!0&&L.updateMatrixWorld(),W.enabled===!0&&W.isPresenting===!0&&(W.cameraAutoUpdate===!0&&W.updateCamera(L),L=W.getCamera()),M.isScene===!0&&M.onBeforeRender(S,M,L,T),u=Ht.get(M,b.length),u.init(L),b.push(u),rt.multiplyMatrices(L.projectionMatrix,L.matrixWorldInverse),Vt.setFromProjectionMatrix(rt),J=this.localClippingEnabled,X=j.init(this.clippingPlanes,J),_=dt.get(M,f.length),_.init(),f.push(_),W.enabled===!0&&W.isPresenting===!0){let tt=S.xr.getDepthSensingMesh();tt!==null&&Jo(tt,L,-1/0,S.sortObjects)}Jo(M,L,0,S.sortObjects),_.finish(),S.sortObjects===!0&&_.sort(z,it),Et=W.enabled===!1||W.isPresenting===!1||W.hasDepthSensing()===!1,Et&&Rt.addToRenderList(_,M),this.info.render.frame++,X===!0&&j.beginShadows();let O=u.state.shadowsArray;mt.render(O,M,L),X===!0&&j.endShadows(),this.info.autoReset===!0&&this.info.reset();let B=_.opaque,D=_.transmissive;if(u.setupLights(),L.isArrayCamera){let tt=L.cameras;if(D.length>0)for(let ct=0,xt=tt.length;ct<xt;ct++){let _t=tt[ct];Nc(B,D,M,_t)}Et&&Rt.render(M);for(let ct=0,xt=tt.length;ct<xt;ct++){let _t=tt[ct];Uc(_,M,_t,_t.viewport)}}else D.length>0&&Nc(B,D,M,L),Et&&Rt.render(M),Uc(_,M,L);T!==null&&(w.updateMultisampleRenderTarget(T),w.updateRenderTargetMipmap(T)),M.isScene===!0&&M.onAfterRender(S,M,L),Jt.resetDefaultState(),P=-1,G=null,b.pop(),b.length>0?(u=b[b.length-1],X===!0&&j.setGlobalState(S.clippingPlanes,u.state.camera)):u=null,f.pop(),f.length>0?_=f[f.length-1]:_=null};function Jo(M,L,O,B){if(M.visible===!1)return;if(M.layers.test(L.layers)){if(M.isGroup)O=M.renderOrder;else if(M.isLOD)M.autoUpdate===!0&&M.update(L);else if(M.isLight)u.pushLight(M),M.castShadow&&u.pushShadow(M);else if(M.isSprite){if(!M.frustumCulled||Vt.intersectsSprite(M)){B&&et.setFromMatrixPosition(M.matrixWorld).applyMatrix4(rt);let ct=q.update(M),xt=M.material;xt.visible&&_.push(M,ct,xt,O,et.z,null)}}else if((M.isMesh||M.isLine||M.isPoints)&&(!M.frustumCulled||Vt.intersectsObject(M))){let ct=q.update(M),xt=M.material;if(B&&(M.boundingSphere!==void 0?(M.boundingSphere===null&&M.computeBoundingSphere(),et.copy(M.boundingSphere.center)):(ct.boundingSphere===null&&ct.computeBoundingSphere(),et.copy(ct.boundingSphere.center)),et.applyMatrix4(M.matrixWorld).applyMatrix4(rt)),Array.isArray(xt)){let _t=ct.groups;for(let At=0,Ct=_t.length;At<Ct;At++){let yt=_t[At],Kt=xt[yt.materialIndex];Kt&&Kt.visible&&_.push(M,ct,Kt,O,et.z,yt)}}else xt.visible&&_.push(M,ct,xt,O,et.z,null)}}let tt=M.children;for(let ct=0,xt=tt.length;ct<xt;ct++)Jo(tt[ct],L,O,B)}function Uc(M,L,O,B){let D=M.opaque,tt=M.transmissive,ct=M.transparent;u.setupLightsView(O),X===!0&&j.setGlobalState(S.clippingPlanes,O),B&&wt.viewport(x.copy(B)),D.length>0&&Ys(D,L,O),tt.length>0&&Ys(tt,L,O),ct.length>0&&Ys(ct,L,O),wt.buffers.depth.setTest(!0),wt.buffers.depth.setMask(!0),wt.buffers.color.setMask(!0),wt.setPolygonOffset(!1)}function Nc(M,L,O,B){if((O.isScene===!0?O.overrideMaterial:null)!==null)return;u.state.transmissionRenderTarget[B.id]===void 0&&(u.state.transmissionRenderTarget[B.id]=new En(1,1,{generateMipmaps:!0,type:Ut.has("EXT_color_buffer_half_float")||Ut.has("EXT_color_buffer_float")?Xs:bn,minFilter:di,samples:4,stencilBuffer:r,resolveDepthBuffer:!1,resolveStencilBuffer:!1,colorSpace:Yt.workingColorSpace}));let tt=u.state.transmissionRenderTarget[B.id],ct=B.viewport||x;tt.setSize(ct.z,ct.w);let xt=S.getRenderTarget();S.setRenderTarget(tt),S.getClearColor(U),H=S.getClearAlpha(),H<1&&S.setClearColor(16777215,.5),S.clear(),Et&&Rt.render(O);let _t=S.toneMapping;S.toneMapping=Wn;let At=B.viewport;if(B.viewport!==void 0&&(B.viewport=void 0),u.setupLightsView(B),X===!0&&j.setGlobalState(S.clippingPlanes,B),Ys(M,O,B),w.updateMultisampleRenderTarget(tt),w.updateRenderTargetMipmap(tt),Ut.has("WEBGL_multisampled_render_to_texture")===!1){let Ct=!1;for(let yt=0,Kt=L.length;yt<Kt;yt++){let Qt=L[yt],re=Qt.object,Ie=Qt.geometry,Xt=Qt.material,Mt=Qt.group;if(Xt.side===Je&&re.layers.test(B.layers)){let me=Xt.side;Xt.side=Re,Xt.needsUpdate=!0,Fc(re,O,B,Ie,Xt,Mt),Xt.side=me,Xt.needsUpdate=!0,Ct=!0}}Ct===!0&&(w.updateMultisampleRenderTarget(tt),w.updateRenderTargetMipmap(tt))}S.setRenderTarget(xt),S.setClearColor(U,H),At!==void 0&&(B.viewport=At),S.toneMapping=_t}function Ys(M,L,O){let B=L.isScene===!0?L.overrideMaterial:null;for(let D=0,tt=M.length;D<tt;D++){let ct=M[D],xt=ct.object,_t=ct.geometry,At=B===null?ct.material:B,Ct=ct.group;xt.layers.test(O.layers)&&Fc(xt,L,O,_t,At,Ct)}}function Fc(M,L,O,B,D,tt){M.onBeforeRender(S,L,O,B,D,tt),M.modelViewMatrix.multiplyMatrices(O.matrixWorldInverse,M.matrixWorld),M.normalMatrix.getNormalMatrix(M.modelViewMatrix),D.onBeforeRender(S,L,O,B,M,tt),D.transparent===!0&&D.side===Je&&D.forceSinglePass===!1?(D.side=Re,D.needsUpdate=!0,S.renderBufferDirect(O,L,B,D,M,tt),D.side=Xn,D.needsUpdate=!0,S.renderBufferDirect(O,L,B,D,M,tt),D.side=Je):S.renderBufferDirect(O,L,B,D,M,tt),M.onAfterRender(S,L,O,B,D,tt)}function Ks(M,L,O){L.isScene!==!0&&(L=at);let B=Tt.get(M),D=u.state.lights,tt=u.state.shadowsArray,ct=D.state.version,xt=vt.getParameters(M,D.state,tt,L,O),_t=vt.getProgramCacheKey(xt),At=B.programs;B.environment=M.isMeshStandardMaterial?L.environment:null,B.fog=L.fog,B.envMap=(M.isMeshStandardMaterial?N:v).get(M.envMap||B.environment),B.envMapRotation=B.environment!==null&&M.envMap===null?L.environmentRotation:M.envMapRotation,At===void 0&&(M.addEventListener("dispose",kt),At=new Map,B.programs=At);let Ct=At.get(_t);if(Ct!==void 0){if(B.currentProgram===Ct&&B.lightsStateVersion===ct)return Bc(M,xt),Ct}else xt.uniforms=vt.getUniforms(M),M.onBeforeCompile(xt,S),Ct=vt.acquireProgram(xt,_t),At.set(_t,Ct),B.uniforms=xt.uniforms;let yt=B.uniforms;return(!M.isShaderMaterial&&!M.isRawShaderMaterial||M.clipping===!0)&&(yt.clippingPlanes=j.uniform),Bc(M,xt),B.needsLights=ud(M),B.lightsStateVersion=ct,B.needsLights&&(yt.ambientLightColor.value=D.state.ambient,yt.lightProbe.value=D.state.probe,yt.directionalLights.value=D.state.directional,yt.directionalLightShadows.value=D.state.directionalShadow,yt.spotLights.value=D.state.spot,yt.spotLightShadows.value=D.state.spotShadow,yt.rectAreaLights.value=D.state.rectArea,yt.ltc_1.value=D.state.rectAreaLTC1,yt.ltc_2.value=D.state.rectAreaLTC2,yt.pointLights.value=D.state.point,yt.pointLightShadows.value=D.state.pointShadow,yt.hemisphereLights.value=D.state.hemi,yt.directionalShadowMap.value=D.state.directionalShadowMap,yt.directionalShadowMatrix.value=D.state.directionalShadowMatrix,yt.spotShadowMap.value=D.state.spotShadowMap,yt.spotLightMatrix.value=D.state.spotLightMatrix,yt.spotLightMap.value=D.state.spotLightMap,yt.pointShadowMap.value=D.state.pointShadowMap,yt.pointShadowMatrix.value=D.state.pointShadowMatrix),B.currentProgram=Ct,B.uniformsList=null,Ct}function Oc(M){if(M.uniformsList===null){let L=M.currentProgram.getUniforms();M.uniformsList=as.seqWithValue(L.seq,M.uniforms)}return M.uniformsList}function Bc(M,L){let O=Tt.get(M);O.outputColorSpace=L.outputColorSpace,O.batching=L.batching,O.batchingColor=L.batchingColor,O.instancing=L.instancing,O.instancingColor=L.instancingColor,O.instancingMorph=L.instancingMorph,O.skinning=L.skinning,O.morphTargets=L.morphTargets,O.morphNormals=L.morphNormals,O.morphColors=L.morphColors,O.morphTargetsCount=L.morphTargetsCount,O.numClippingPlanes=L.numClippingPlanes,O.numIntersection=L.numClipIntersection,O.vertexAlphas=L.vertexAlphas,O.vertexTangents=L.vertexTangents,O.toneMapping=L.toneMapping}function cd(M,L,O,B,D){L.isScene!==!0&&(L=at),w.resetTextureUnits();let tt=L.fog,ct=B.isMeshStandardMaterial?L.environment:null,xt=T===null?S.outputColorSpace:T.isXRRenderTarget===!0?T.texture.colorSpace:$n,_t=(B.isMeshStandardMaterial?N:v).get(B.envMap||ct),At=B.vertexColors===!0&&!!O.attributes.color&&O.attributes.color.itemSize===4,Ct=!!O.attributes.tangent&&(!!B.normalMap||B.anisotropy>0),yt=!!O.morphAttributes.position,Kt=!!O.morphAttributes.normal,Qt=!!O.morphAttributes.color,re=Wn;B.toneMapped&&(T===null||T.isXRRenderTarget===!0)&&(re=S.toneMapping);let Ie=O.morphAttributes.position||O.morphAttributes.normal||O.morphAttributes.color,Xt=Ie!==void 0?Ie.length:0,Mt=Tt.get(B),me=u.state.lights;if(X===!0&&(J===!0||M!==G)){let Ve=M===G&&B.id===P;j.setState(B,M,Ve)}let qt=!1;B.version===Mt.__version?(Mt.needsLights&&Mt.lightsStateVersion!==me.state.version||Mt.outputColorSpace!==xt||D.isBatchedMesh&&Mt.batching===!1||!D.isBatchedMesh&&Mt.batching===!0||D.isBatchedMesh&&Mt.batchingColor===!0&&D.colorTexture===null||D.isBatchedMesh&&Mt.batchingColor===!1&&D.colorTexture!==null||D.isInstancedMesh&&Mt.instancing===!1||!D.isInstancedMesh&&Mt.instancing===!0||D.isSkinnedMesh&&Mt.skinning===!1||!D.isSkinnedMesh&&Mt.skinning===!0||D.isInstancedMesh&&Mt.instancingColor===!0&&D.instanceColor===null||D.isInstancedMesh&&Mt.instancingColor===!1&&D.instanceColor!==null||D.isInstancedMesh&&Mt.instancingMorph===!0&&D.morphTexture===null||D.isInstancedMesh&&Mt.instancingMorph===!1&&D.morphTexture!==null||Mt.envMap!==_t||B.fog===!0&&Mt.fog!==tt||Mt.numClippingPlanes!==void 0&&(Mt.numClippingPlanes!==j.numPlanes||Mt.numIntersection!==j.numIntersection)||Mt.vertexAlphas!==At||Mt.vertexTangents!==Ct||Mt.morphTargets!==yt||Mt.morphNormals!==Kt||Mt.morphColors!==Qt||Mt.toneMapping!==re||Mt.morphTargetsCount!==Xt)&&(qt=!0):(qt=!0,Mt.__version=B.version);let qe=Mt.currentProgram;qt===!0&&(qe=Ks(B,L,D));let yi=!1,Le=!1,Qo=!1,oe=qe.getUniforms(),Tn=Mt.uniforms;if(wt.useProgram(qe.program)&&(yi=!0,Le=!0,Qo=!0),B.id!==P&&(P=B.id,Le=!0),yi||G!==M){Ot.reverseDepthBuffer?(ht.copy(M.projectionMatrix),Tm(ht),Am(ht),oe.setValue(R,"projectionMatrix",ht)):oe.setValue(R,"projectionMatrix",M.projectionMatrix),oe.setValue(R,"viewMatrix",M.matrixWorldInverse);let Ve=oe.map.cameraPosition;Ve!==void 0&&Ve.setValue(R,bt.setFromMatrixPosition(M.matrixWorld)),Ot.logarithmicDepthBuffer&&oe.setValue(R,"logDepthBufFC",2/(Math.log(M.far+1)/Math.LN2)),(B.isMeshPhongMaterial||B.isMeshToonMaterial||B.isMeshLambertMaterial||B.isMeshBasicMaterial||B.isMeshStandardMaterial||B.isShaderMaterial)&&oe.setValue(R,"isOrthographic",M.isOrthographicCamera===!0),G!==M&&(G=M,Le=!0,Qo=!0)}if(D.isSkinnedMesh){oe.setOptional(R,D,"bindMatrix"),oe.setOptional(R,D,"bindMatrixInverse");let Ve=D.skeleton;Ve&&(Ve.boneTexture===null&&Ve.computeBoneTexture(),oe.setValue(R,"boneTexture",Ve.boneTexture,w))}D.isBatchedMesh&&(oe.setOptional(R,D,"batchingTexture"),oe.setValue(R,"batchingTexture",D._matricesTexture,w),oe.setOptional(R,D,"batchingIdTexture"),oe.setValue(R,"batchingIdTexture",D._indirectTexture,w),oe.setOptional(R,D,"batchingColorTexture"),D._colorsTexture!==null&&oe.setValue(R,"batchingColorTexture",D._colorsTexture,w));let jo=O.morphAttributes;if((jo.position!==void 0||jo.normal!==void 0||jo.color!==void 0)&&Pt.update(D,O,qe),(Le||Mt.receiveShadow!==D.receiveShadow)&&(Mt.receiveShadow=D.receiveShadow,oe.setValue(R,"receiveShadow",D.receiveShadow)),B.isMeshGouraudMaterial&&B.envMap!==null&&(Tn.envMap.value=_t,Tn.flipEnvMap.value=_t.isCubeTexture&&_t.isRenderTargetTexture===!1?-1:1),B.isMeshStandardMaterial&&B.envMap===null&&L.environment!==null&&(Tn.envMapIntensity.value=L.environmentIntensity),Le&&(oe.setValue(R,"toneMappingExposure",S.toneMappingExposure),Mt.needsLights&&hd(Tn,Qo),tt&&B.fog===!0&&st.refreshFogUniforms(Tn,tt),st.refreshMaterialUniforms(Tn,B,Z,k,u.state.transmissionRenderTarget[M.id]),as.upload(R,Oc(Mt),Tn,w)),B.isShaderMaterial&&B.uniformsNeedUpdate===!0&&(as.upload(R,Oc(Mt),Tn,w),B.uniformsNeedUpdate=!1),B.isSpriteMaterial&&oe.setValue(R,"center",D.center),oe.setValue(R,"modelViewMatrix",D.modelViewMatrix),oe.setValue(R,"normalMatrix",D.normalMatrix),oe.setValue(R,"modelMatrix",D.matrixWorld),B.isShaderMaterial||B.isRawShaderMaterial){let Ve=B.uniformsGroups;for(let ta=0,fd=Ve.length;ta<fd;ta++){let Vc=Ve[ta];I.update(Vc,qe),I.bind(Vc,qe)}}return qe}function hd(M,L){M.ambientLightColor.needsUpdate=L,M.lightProbe.needsUpdate=L,M.directionalLights.needsUpdate=L,M.directionalLightShadows.needsUpdate=L,M.pointLights.needsUpdate=L,M.pointLightShadows.needsUpdate=L,M.spotLights.needsUpdate=L,M.spotLightShadows.needsUpdate=L,M.rectAreaLights.needsUpdate=L,M.hemisphereLights.needsUpdate=L}function ud(M){return M.isMeshLambertMaterial||M.isMeshToonMaterial||M.isMeshPhongMaterial||M.isMeshStandardMaterial||M.isShadowMaterial||M.isShaderMaterial&&M.lights===!0}this.getActiveCubeFace=function(){return C},this.getActiveMipmapLevel=function(){return A},this.getRenderTarget=function(){return T},this.setRenderTargetTextures=function(M,L,O){Tt.get(M.texture).__webglTexture=L,Tt.get(M.depthTexture).__webglTexture=O;let B=Tt.get(M);B.__hasExternalTextures=!0,B.__autoAllocateDepthBuffer=O===void 0,B.__autoAllocateDepthBuffer||Ut.has("WEBGL_multisampled_render_to_texture")===!0&&(console.warn("THREE.WebGLRenderer: Render-to-texture extension was disabled because an external texture was provided"),B.__useRenderToTexture=!1)},this.setRenderTargetFramebuffer=function(M,L){let O=Tt.get(M);O.__webglFramebuffer=L,O.__useDefaultFramebuffer=L===void 0},this.setRenderTarget=function(M,L=0,O=0){T=M,C=L,A=O;let B=!0,D=null,tt=!1,ct=!1;if(M){let _t=Tt.get(M);if(_t.__useDefaultFramebuffer!==void 0)wt.bindFramebuffer(R.FRAMEBUFFER,null),B=!1;else if(_t.__webglFramebuffer===void 0)w.setupRenderTarget(M);else if(_t.__hasExternalTextures)w.rebindTextures(M,Tt.get(M.texture).__webglTexture,Tt.get(M.depthTexture).__webglTexture);else if(M.depthBuffer){let yt=M.depthTexture;if(_t.__boundDepthTexture!==yt){if(yt!==null&&Tt.has(yt)&&(M.width!==yt.image.width||M.height!==yt.image.height))throw new Error("WebGLRenderTarget: Attached DepthTexture is initialized to the incorrect size.");w.setupDepthRenderbuffer(M)}}let At=M.texture;(At.isData3DTexture||At.isDataArrayTexture||At.isCompressedArrayTexture)&&(ct=!0);let Ct=Tt.get(M).__webglFramebuffer;M.isWebGLCubeRenderTarget?(Array.isArray(Ct[L])?D=Ct[L][O]:D=Ct[L],tt=!0):M.samples>0&&w.useMultisampledRTT(M)===!1?D=Tt.get(M).__webglMultisampledFramebuffer:Array.isArray(Ct)?D=Ct[O]:D=Ct,x.copy(M.viewport),y.copy(M.scissor),V=M.scissorTest}else x.copy(ot).multiplyScalar(Z).floor(),y.copy(pt).multiplyScalar(Z).floor(),V=Bt;if(wt.bindFramebuffer(R.FRAMEBUFFER,D)&&B&&wt.drawBuffers(M,D),wt.viewport(x),wt.scissor(y),wt.setScissorTest(V),tt){let _t=Tt.get(M.texture);R.framebufferTexture2D(R.FRAMEBUFFER,R.COLOR_ATTACHMENT0,R.TEXTURE_CUBE_MAP_POSITIVE_X+L,_t.__webglTexture,O)}else if(ct){let _t=Tt.get(M.texture),At=L||0;R.framebufferTextureLayer(R.FRAMEBUFFER,R.COLOR_ATTACHMENT0,_t.__webglTexture,O||0,At)}P=-1},this.readRenderTargetPixels=function(M,L,O,B,D,tt,ct){if(!(M&&M.isWebGLRenderTarget)){console.error("THREE.WebGLRenderer.readRenderTargetPixels: renderTarget is not THREE.WebGLRenderTarget.");return}let xt=Tt.get(M).__webglFramebuffer;if(M.isWebGLCubeRenderTarget&&ct!==void 0&&(xt=xt[ct]),xt){wt.bindFramebuffer(R.FRAMEBUFFER,xt);try{let _t=M.texture,At=_t.format,Ct=_t.type;if(!Ot.textureFormatReadable(At)){console.error("THREE.WebGLRenderer.readRenderTargetPixels: renderTarget is not in RGBA or implementation defined format.");return}if(!Ot.textureTypeReadable(Ct)){console.error("THREE.WebGLRenderer.readRenderTargetPixels: renderTarget is not in UnsignedByteType or implementation defined type.");return}L>=0&&L<=M.width-B&&O>=0&&O<=M.height-D&&R.readPixels(L,O,B,D,Lt.convert(At),Lt.convert(Ct),tt)}finally{let _t=T!==null?Tt.get(T).__webglFramebuffer:null;wt.bindFramebuffer(R.FRAMEBUFFER,_t)}}},this.readRenderTargetPixelsAsync=async function(M,L,O,B,D,tt,ct){if(!(M&&M.isWebGLRenderTarget))throw new Error("THREE.WebGLRenderer.readRenderTargetPixels: renderTarget is not THREE.WebGLRenderTarget.");let xt=Tt.get(M).__webglFramebuffer;if(M.isWebGLCubeRenderTarget&&ct!==void 0&&(xt=xt[ct]),xt){let _t=M.texture,At=_t.format,Ct=_t.type;if(!Ot.textureFormatReadable(At))throw new Error("THREE.WebGLRenderer.readRenderTargetPixelsAsync: renderTarget is not in RGBA or implementation defined format.");if(!Ot.textureTypeReadable(Ct))throw new Error("THREE.WebGLRenderer.readRenderTargetPixelsAsync: renderTarget is not in UnsignedByteType or implementation defined type.");if(L>=0&&L<=M.width-B&&O>=0&&O<=M.height-D){wt.bindFramebuffer(R.FRAMEBUFFER,xt);let yt=R.createBuffer();R.bindBuffer(R.PIXEL_PACK_BUFFER,yt),R.bufferData(R.PIXEL_PACK_BUFFER,tt.byteLength,R.STREAM_READ),R.readPixels(L,O,B,D,Lt.convert(At),Lt.convert(Ct),0);let Kt=T!==null?Tt.get(T).__webglFramebuffer:null;wt.bindFramebuffer(R.FRAMEBUFFER,Kt);let Qt=R.fenceSync(R.SYNC_GPU_COMMANDS_COMPLETE,0);return R.flush(),await wm(R,Qt,4),R.bindBuffer(R.PIXEL_PACK_BUFFER,yt),R.getBufferSubData(R.PIXEL_PACK_BUFFER,0,tt),R.deleteBuffer(yt),R.deleteSync(Qt),tt}else throw new Error("THREE.WebGLRenderer.readRenderTargetPixelsAsync: requested read bounds are out of range.")}},this.copyFramebufferToTexture=function(M,L=null,O=0){M.isTexture!==!0&&(yo("WebGLRenderer: copyFramebufferToTexture function signature has changed."),L=arguments[0]||null,M=arguments[1]);let B=Math.pow(2,-O),D=Math.floor(M.image.width*B),tt=Math.floor(M.image.height*B),ct=L!==null?L.x:0,xt=L!==null?L.y:0;w.setTexture2D(M,0),R.copyTexSubImage2D(R.TEXTURE_2D,O,0,0,ct,xt,D,tt),wt.unbindTexture()},this.copyTextureToTexture=function(M,L,O=null,B=null,D=0){M.isTexture!==!0&&(yo("WebGLRenderer: copyTextureToTexture function signature has changed."),B=arguments[0]||null,M=arguments[1],L=arguments[2],D=arguments[3]||0,O=null);let tt,ct,xt,_t,At,Ct;O!==null?(tt=O.max.x-O.min.x,ct=O.max.y-O.min.y,xt=O.min.x,_t=O.min.y):(tt=M.image.width,ct=M.image.height,xt=0,_t=0),B!==null?(At=B.x,Ct=B.y):(At=0,Ct=0);let yt=Lt.convert(L.format),Kt=Lt.convert(L.type);w.setTexture2D(L,0),R.pixelStorei(R.UNPACK_FLIP_Y_WEBGL,L.flipY),R.pixelStorei(R.UNPACK_PREMULTIPLY_ALPHA_WEBGL,L.premultiplyAlpha),R.pixelStorei(R.UNPACK_ALIGNMENT,L.unpackAlignment);let Qt=R.getParameter(R.UNPACK_ROW_LENGTH),re=R.getParameter(R.UNPACK_IMAGE_HEIGHT),Ie=R.getParameter(R.UNPACK_SKIP_PIXELS),Xt=R.getParameter(R.UNPACK_SKIP_ROWS),Mt=R.getParameter(R.UNPACK_SKIP_IMAGES),me=M.isCompressedTexture?M.mipmaps[D]:M.image;R.pixelStorei(R.UNPACK_ROW_LENGTH,me.width),R.pixelStorei(R.UNPACK_IMAGE_HEIGHT,me.height),R.pixelStorei(R.UNPACK_SKIP_PIXELS,xt),R.pixelStorei(R.UNPACK_SKIP_ROWS,_t),M.isDataTexture?R.texSubImage2D(R.TEXTURE_2D,D,At,Ct,tt,ct,yt,Kt,me.data):M.isCompressedTexture?R.compressedTexSubImage2D(R.TEXTURE_2D,D,At,Ct,me.width,me.height,yt,me.data):R.texSubImage2D(R.TEXTURE_2D,D,At,Ct,tt,ct,yt,Kt,me),R.pixelStorei(R.UNPACK_ROW_LENGTH,Qt),R.pixelStorei(R.UNPACK_IMAGE_HEIGHT,re),R.pixelStorei(R.UNPACK_SKIP_PIXELS,Ie),R.pixelStorei(R.UNPACK_SKIP_ROWS,Xt),R.pixelStorei(R.UNPACK_SKIP_IMAGES,Mt),D===0&&L.generateMipmaps&&R.generateMipmap(R.TEXTURE_2D),wt.unbindTexture()},this.copyTextureToTexture3D=function(M,L,O=null,B=null,D=0){M.isTexture!==!0&&(yo("WebGLRenderer: copyTextureToTexture3D function signature has changed."),O=arguments[0]||null,B=arguments[1]||null,M=arguments[2],L=arguments[3],D=arguments[4]||0);let tt,ct,xt,_t,At,Ct,yt,Kt,Qt,re=M.isCompressedTexture?M.mipmaps[D]:M.image;O!==null?(tt=O.max.x-O.min.x,ct=O.max.y-O.min.y,xt=O.max.z-O.min.z,_t=O.min.x,At=O.min.y,Ct=O.min.z):(tt=re.width,ct=re.height,xt=re.depth,_t=0,At=0,Ct=0),B!==null?(yt=B.x,Kt=B.y,Qt=B.z):(yt=0,Kt=0,Qt=0);let Ie=Lt.convert(L.format),Xt=Lt.convert(L.type),Mt;if(L.isData3DTexture)w.setTexture3D(L,0),Mt=R.TEXTURE_3D;else if(L.isDataArrayTexture||L.isCompressedArrayTexture)w.setTexture2DArray(L,0),Mt=R.TEXTURE_2D_ARRAY;else{console.warn("THREE.WebGLRenderer.copyTextureToTexture3D: only supports THREE.DataTexture3D and THREE.DataTexture2DArray.");return}R.pixelStorei(R.UNPACK_FLIP_Y_WEBGL,L.flipY),R.pixelStorei(R.UNPACK_PREMULTIPLY_ALPHA_WEBGL,L.premultiplyAlpha),R.pixelStorei(R.UNPACK_ALIGNMENT,L.unpackAlignment);let me=R.getParameter(R.UNPACK_ROW_LENGTH),qt=R.getParameter(R.UNPACK_IMAGE_HEIGHT),qe=R.getParameter(R.UNPACK_SKIP_PIXELS),yi=R.getParameter(R.UNPACK_SKIP_ROWS),Le=R.getParameter(R.UNPACK_SKIP_IMAGES);R.pixelStorei(R.UNPACK_ROW_LENGTH,re.width),R.pixelStorei(R.UNPACK_IMAGE_HEIGHT,re.height),R.pixelStorei(R.UNPACK_SKIP_PIXELS,_t),R.pixelStorei(R.UNPACK_SKIP_ROWS,At),R.pixelStorei(R.UNPACK_SKIP_IMAGES,Ct),M.isDataTexture||M.isData3DTexture?R.texSubImage3D(Mt,D,yt,Kt,Qt,tt,ct,xt,Ie,Xt,re.data):L.isCompressedArrayTexture?R.compressedTexSubImage3D(Mt,D,yt,Kt,Qt,tt,ct,xt,Ie,re.data):R.texSubImage3D(Mt,D,yt,Kt,Qt,tt,ct,xt,Ie,Xt,re),R.pixelStorei(R.UNPACK_ROW_LENGTH,me),R.pixelStorei(R.UNPACK_IMAGE_HEIGHT,qt),R.pixelStorei(R.UNPACK_SKIP_PIXELS,qe),R.pixelStorei(R.UNPACK_SKIP_ROWS,yi),R.pixelStorei(R.UNPACK_SKIP_IMAGES,Le),D===0&&L.generateMipmaps&&R.generateMipmap(Mt),wt.unbindTexture()},this.initRenderTarget=function(M){Tt.get(M).__webglFramebuffer===void 0&&w.setupRenderTarget(M)},this.initTexture=function(M){M.isCubeTexture?w.setTextureCube(M,0):M.isData3DTexture?w.setTexture3D(M,0):M.isDataArrayTexture||M.isCompressedArrayTexture?w.setTexture2DArray(M,0):w.setTexture2D(M,0),wt.unbindTexture()},this.resetState=function(){C=0,A=0,T=null,wt.reset(),Jt.reset()},typeof __THREE_DEVTOOLS__!="undefined"&&__THREE_DEVTOOLS__.dispatchEvent(new CustomEvent("observe",{detail:this}))}get coordinateSystem(){return Sn}get outputColorSpace(){return this._outputColorSpace}set outputColorSpace(t){this._outputColorSpace=t;let e=this.getContext();e.drawingBufferColorSpace=t===Ac?"display-p3":"srgb",e.unpackColorSpace=Yt.workingColorSpace===Ko?"display-p3":"srgb"}};var zo=class extends Oe{constructor(){super(),this.isScene=!0,this.type="Scene",this.background=null,this.environment=null,this.fog=null,this.backgroundBlurriness=0,this.backgroundIntensity=1,this.backgroundRotation=new wn,this.environmentIntensity=1,this.environmentRotation=new wn,this.overrideMaterial=null,typeof __THREE_DEVTOOLS__!="undefined"&&__THREE_DEVTOOLS__.dispatchEvent(new CustomEvent("observe",{detail:this}))}copy(t,e){return super.copy(t,e),t.background!==null&&(this.background=t.background.clone()),t.environment!==null&&(this.environment=t.environment.clone()),t.fog!==null&&(this.fog=t.fog.clone()),this.backgroundBlurriness=t.backgroundBlurriness,this.backgroundIntensity=t.backgroundIntensity,this.backgroundRotation.copy(t.backgroundRotation),this.environmentIntensity=t.environmentIntensity,this.environmentRotation.copy(t.environmentRotation),t.overrideMaterial!==null&&(this.overrideMaterial=t.overrideMaterial.clone()),this.matrixAutoUpdate=t.matrixAutoUpdate,this}toJSON(t){let e=super.toJSON(t);return this.fog!==null&&(e.object.fog=this.fog.toJSON()),this.backgroundBlurriness>0&&(e.object.backgroundBlurriness=this.backgroundBlurriness),this.backgroundIntensity!==1&&(e.object.backgroundIntensity=this.backgroundIntensity),e.object.backgroundRotation=this.backgroundRotation.toArray(),this.environmentIntensity!==1&&(e.object.environmentIntensity=this.environmentIntensity),e.object.environmentRotation=this.environmentRotation.toArray(),e}};var Gs=class extends Kn{constructor(t){super(),this.isLineBasicMaterial=!0,this.type="LineBasicMaterial",this.color=new Gt(16777215),this.map=null,this.linewidth=1,this.linecap="round",this.linejoin="round",this.fog=!0,this.setValues(t)}copy(t){return super.copy(t),this.color.copy(t.color),this.map=t.map,this.linewidth=t.linewidth,this.linecap=t.linecap,this.linejoin=t.linejoin,this.fog=t.fog,this}},ko=new F,Ho=new F,Cf=new le,Os=new ks,co=new gi,il=new F,Rf=new F,lc=class extends Oe{constructor(t=new Be,e=new Gs){super(),this.isLine=!0,this.type="Line",this.geometry=t,this.material=e,this.updateMorphTargets()}copy(t,e){return super.copy(t,e),this.material=Array.isArray(t.material)?t.material.slice():t.material,this.geometry=t.geometry,this}computeLineDistances(){let t=this.geometry;if(t.index===null){let e=t.attributes.position,i=[0];for(let s=1,r=e.count;s<r;s++)ko.fromBufferAttribute(e,s-1),Ho.fromBufferAttribute(e,s),i[s]=i[s-1],i[s]+=ko.distanceTo(Ho);t.setAttribute("lineDistance",new Pe(i,1))}else console.warn("THREE.Line.computeLineDistances(): Computation only possible with non-indexed BufferGeometry.");return this}raycast(t,e){let i=this.geometry,s=this.matrixWorld,r=t.params.Line.threshold,o=i.drawRange;if(i.boundingSphere===null&&i.computeBoundingSphere(),co.copy(i.boundingSphere),co.applyMatrix4(s),co.radius+=r,t.ray.intersectsSphere(co)===!1)return;Cf.copy(s).invert(),Os.copy(t.ray).applyMatrix4(Cf);let a=r/((this.scale.x+this.scale.y+this.scale.z)/3),l=a*a,c=this.isLineSegments?2:1,h=i.index,p=i.attributes.position;if(h!==null){let m=Math.max(0,o.start),g=Math.min(h.count,o.start+o.count);for(let _=m,u=g-1;_<u;_+=c){let f=h.getX(_),b=h.getX(_+1),S=ho(this,t,Os,l,f,b);S&&e.push(S)}if(this.isLineLoop){let _=h.getX(g-1),u=h.getX(m),f=ho(this,t,Os,l,_,u);f&&e.push(f)}}else{let m=Math.max(0,o.start),g=Math.min(p.count,o.start+o.count);for(let _=m,u=g-1;_<u;_+=c){let f=ho(this,t,Os,l,_,_+1);f&&e.push(f)}if(this.isLineLoop){let _=ho(this,t,Os,l,g-1,m);_&&e.push(_)}}}updateMorphTargets(){let e=this.geometry.morphAttributes,i=Object.keys(e);if(i.length>0){let s=e[i[0]];if(s!==void 0){this.morphTargetInfluences=[],this.morphTargetDictionary={};for(let r=0,o=s.length;r<o;r++){let a=s[r].name||String(r);this.morphTargetInfluences.push(0),this.morphTargetDictionary[a]=r}}}}};function ho(n,t,e,i,s,r){let o=n.geometry.attributes.position;if(ko.fromBufferAttribute(o,s),Ho.fromBufferAttribute(o,r),e.distanceSqToSegment(ko,Ho,il,Rf)>i)return;il.applyMatrix4(n.matrixWorld);let l=t.ray.origin.distanceTo(il);if(!(l<t.near||l>t.far))return{distance:l,point:Rf.clone().applyMatrix4(n.matrixWorld),index:s,face:null,faceIndex:null,barycoord:null,object:n}}var Pf=new F,If=new F,Go=class extends lc{constructor(t,e){super(t,e),this.isLineSegments=!0,this.type="LineSegments"}computeLineDistances(){let t=this.geometry;if(t.index===null){let e=t.attributes.position,i=[];for(let s=0,r=e.count;s<r;s+=2)Pf.fromBufferAttribute(e,s),If.fromBufferAttribute(e,s+1),i[s]=s===0?0:i[s-1],i[s+1]=i[s]+Pf.distanceTo(If);t.setAttribute("lineDistance",new Pe(i,1))}else console.warn("THREE.LineSegments.computeLineDistances(): Computation only possible with non-indexed BufferGeometry.");return this}};var ms=class extends Kn{constructor(t){super(),this.isPointsMaterial=!0,this.type="PointsMaterial",this.color=new Gt(16777215),this.map=null,this.alphaMap=null,this.size=1,this.sizeAttenuation=!0,this.fog=!0,this.setValues(t)}copy(t){return super.copy(t),this.color.copy(t.color),this.map=t.map,this.alphaMap=t.alphaMap,this.size=t.size,this.sizeAttenuation=t.sizeAttenuation,this.fog=t.fog,this}},Lf=new le,cc=new ks,uo=new gi,fo=new F,Ws=class extends Oe{constructor(t=new Be,e=new ms){super(),this.isPoints=!0,this.type="Points",this.geometry=t,this.material=e,this.updateMorphTargets()}copy(t,e){return super.copy(t,e),this.material=Array.isArray(t.material)?t.material.slice():t.material,this.geometry=t.geometry,this}raycast(t,e){let i=this.geometry,s=this.matrixWorld,r=t.params.Points.threshold,o=i.drawRange;if(i.boundingSphere===null&&i.computeBoundingSphere(),uo.copy(i.boundingSphere),uo.applyMatrix4(s),uo.radius+=r,t.ray.intersectsSphere(uo)===!1)return;Lf.copy(s).invert(),cc.copy(t.ray).applyMatrix4(Lf);let a=r/((this.scale.x+this.scale.y+this.scale.z)/3),l=a*a,c=i.index,d=i.attributes.position;if(c!==null){let p=Math.max(0,o.start),m=Math.min(c.count,o.start+o.count);for(let g=p,_=m;g<_;g++){let u=c.getX(g);fo.fromBufferAttribute(d,u),Df(fo,u,l,s,t,e,this)}}else{let p=Math.max(0,o.start),m=Math.min(d.count,o.start+o.count);for(let g=p,_=m;g<_;g++)fo.fromBufferAttribute(d,g),Df(fo,g,l,s,t,e,this)}}updateMorphTargets(){let e=this.geometry.morphAttributes,i=Object.keys(e);if(i.length>0){let s=e[i[0]];if(s!==void 0){this.morphTargetInfluences=[],this.morphTargetDictionary={};for(let r=0,o=s.length;r<o;r++){let a=s[r].name||String(r);this.morphTargetInfluences.push(0),this.morphTargetDictionary[a]=r}}}}};function Df(n,t,e,i,s,r,o){let a=cc.distanceSqToPoint(n);if(a<e){let l=new F;cc.closestPointToPoint(n,l),l.applyMatrix4(i);let c=s.ray.origin.distanceTo(l);if(c<s.near||c>s.far)return;r.push({distance:c,distanceToRay:Math.sqrt(a),point:l,index:t,face:null,faceIndex:null,barycoord:null,object:o})}}var Wo=class n extends Be{constructor(t=1,e=32,i=0,s=Math.PI*2){super(),this.type="CircleGeometry",this.parameters={radius:t,segments:e,thetaStart:i,thetaLength:s},e=Math.max(3,e);let r=[],o=[],a=[],l=[],c=new F,h=new Zt;o.push(0,0,0),a.push(0,0,1),l.push(.5,.5);for(let d=0,p=3;d<=e;d++,p+=3){let m=i+d/e*s;c.x=t*Math.cos(m),c.y=t*Math.sin(m),o.push(c.x,c.y,c.z),a.push(0,0,1),h.x=(o[p]/t+1)/2,h.y=(o[p+1]/t+1)/2,l.push(h.x,h.y)}for(let d=1;d<=e;d++)r.push(d,d+1,0);this.setIndex(r),this.setAttribute("position",new Pe(o,3)),this.setAttribute("normal",new Pe(a,3)),this.setAttribute("uv",new Pe(l,2))}copy(t){return super.copy(t),this.parameters=Object.assign({},t.parameters),this}static fromJSON(t){return new n(t.radius,t.segments,t.thetaStart,t.thetaLength)}};function po(n,t,e){return!n||!e&&n.constructor===t?n:typeof t.BYTES_PER_ELEMENT=="number"?new t(n):Array.prototype.slice.call(n)}function Sv(n){return ArrayBuffer.isView(n)&&!(n instanceof DataView)}var gs=class{constructor(t,e,i,s){this.parameterPositions=t,this._cachedIndex=0,this.resultBuffer=s!==void 0?s:new e.constructor(i),this.sampleValues=e,this.valueSize=i,this.settings=null,this.DefaultSettings_={}}evaluate(t){let e=this.parameterPositions,i=this._cachedIndex,s=e[i],r=e[i-1];n:{t:{let o;e:{i:if(!(t<s)){for(let a=i+2;;){if(s===void 0){if(t<r)break i;return i=e.length,this._cachedIndex=i,this.copySampleValue_(i-1)}if(i===a)break;if(r=s,s=e[++i],t<s)break t}o=e.length;break e}if(!(t>=r)){let a=e[1];t<a&&(i=2,r=a);for(let l=i-2;;){if(r===void 0)return this._cachedIndex=0,this.copySampleValue_(0);if(i===l)break;if(s=r,r=e[--i-1],t>=r)break t}o=i,i=0;break e}break n}for(;i<o;){let a=i+o>>>1;t<e[a]?o=a:i=a+1}if(s=e[i],r=e[i-1],r===void 0)return this._cachedIndex=0,this.copySampleValue_(0);if(s===void 0)return i=e.length,this._cachedIndex=i,this.copySampleValue_(i-1)}this._cachedIndex=i,this.intervalChanged_(i,r,s)}return this.interpolate_(i,r,t,s)}getSettings_(){return this.settings||this.DefaultSettings_}copySampleValue_(t){let e=this.resultBuffer,i=this.sampleValues,s=this.valueSize,r=t*s;for(let o=0;o!==s;++o)e[o]=i[r+o];return e}interpolate_(){throw new Error("call to abstract method")}intervalChanged_(){}},hc=class extends gs{constructor(t,e,i,s){super(t,e,i,s),this._weightPrev=-0,this._offsetPrev=-0,this._weightNext=-0,this._offsetNext=-0,this.DefaultSettings_={endingStart:Nu,endingEnd:Nu}}intervalChanged_(t,e,i){let s=this.parameterPositions,r=t-2,o=t+1,a=s[r],l=s[o];if(a===void 0)switch(this.getSettings_().endingStart){case Fu:r=t,a=2*e-i;break;case Ou:r=s.length-2,a=e+s[r]-s[r+1];break;default:r=t,a=i}if(l===void 0)switch(this.getSettings_().endingEnd){case Fu:o=t,l=2*i-e;break;case Ou:o=1,l=i+s[1]-s[0];break;default:o=t-1,l=e}let c=(i-e)*.5,h=this.valueSize;this._weightPrev=c/(e-a),this._weightNext=c/(l-i),this._offsetPrev=r*h,this._offsetNext=o*h}interpolate_(t,e,i,s){let r=this.resultBuffer,o=this.sampleValues,a=this.valueSize,l=t*a,c=l-a,h=this._offsetPrev,d=this._offsetNext,p=this._weightPrev,m=this._weightNext,g=(i-e)/(s-e),_=g*g,u=_*g,f=-p*u+2*p*_-p*g,b=(1+p)*u+(-1.5-2*p)*_+(-.5+p)*g+1,S=(-1-m)*u+(1.5+m)*_+.5*g,E=m*u-m*_;for(let C=0;C!==a;++C)r[C]=f*o[h+C]+b*o[c+C]+S*o[l+C]+E*o[d+C];return r}},uc=class extends gs{constructor(t,e,i,s){super(t,e,i,s)}interpolate_(t,e,i,s){let r=this.resultBuffer,o=this.sampleValues,a=this.valueSize,l=t*a,c=l-a,h=(i-e)/(s-e),d=1-h;for(let p=0;p!==a;++p)r[p]=o[c+p]*d+o[l+p]*h;return r}},fc=class extends gs{constructor(t,e,i,s){super(t,e,i,s)}interpolate_(t){return this.copySampleValue_(t-1)}},tn=class{constructor(t,e,i,s){if(t===void 0)throw new Error("THREE.KeyframeTrack: track name is undefined");if(e===void 0||e.length===0)throw new Error("THREE.KeyframeTrack: no keyframes in track named "+t);this.name=t,this.times=po(e,this.TimeBufferType),this.values=po(i,this.ValueBufferType),this.setInterpolation(s||this.DefaultInterpolation)}static toJSON(t){let e=t.constructor,i;if(e.toJSON!==this.toJSON)i=e.toJSON(t);else{i={name:t.name,times:po(t.times,Array),values:po(t.values,Array)};let s=t.getInterpolation();s!==t.DefaultInterpolation&&(i.interpolation=s)}return i.type=t.ValueTypeName,i}InterpolantFactoryMethodDiscrete(t){return new fc(this.times,this.values,this.getValueSize(),t)}InterpolantFactoryMethodLinear(t){return new uc(this.times,this.values,this.getValueSize(),t)}InterpolantFactoryMethodSmooth(t){return new hc(this.times,this.values,this.getValueSize(),t)}setInterpolation(t){let e;switch(t){case So:e=this.InterpolantFactoryMethodDiscrete;break;case Gl:e=this.InterpolantFactoryMethodLinear;break;case Aa:e=this.InterpolantFactoryMethodSmooth;break}if(e===void 0){let i="unsupported interpolation for "+this.ValueTypeName+" keyframe track named "+this.name;if(this.createInterpolant===void 0)if(t!==this.DefaultInterpolation)this.setInterpolation(this.DefaultInterpolation);else throw new Error(i);return console.warn("THREE.KeyframeTrack:",i),this}return this.createInterpolant=e,this}getInterpolation(){switch(this.createInterpolant){case this.InterpolantFactoryMethodDiscrete:return So;case this.InterpolantFactoryMethodLinear:return Gl;case this.InterpolantFactoryMethodSmooth:return Aa}}getValueSize(){return this.values.length/this.times.length}shift(t){if(t!==0){let e=this.times;for(let i=0,s=e.length;i!==s;++i)e[i]+=t}return this}scale(t){if(t!==1){let e=this.times;for(let i=0,s=e.length;i!==s;++i)e[i]*=t}return this}trim(t,e){let i=this.times,s=i.length,r=0,o=s-1;for(;r!==s&&i[r]<t;)++r;for(;o!==-1&&i[o]>e;)--o;if(++o,r!==0||o!==s){r>=o&&(o=Math.max(o,1),r=o-1);let a=this.getValueSize();this.times=i.slice(r,o),this.values=this.values.slice(r*a,o*a)}return this}validate(){let t=!0,e=this.getValueSize();e-Math.floor(e)!==0&&(console.error("THREE.KeyframeTrack: Invalid value size in track.",this),t=!1);let i=this.times,s=this.values,r=i.length;r===0&&(console.error("THREE.KeyframeTrack: Track is empty.",this),t=!1);let o=null;for(let a=0;a!==r;a++){let l=i[a];if(typeof l=="number"&&isNaN(l)){console.error("THREE.KeyframeTrack: Time is not a valid number.",this,a,l),t=!1;break}if(o!==null&&o>l){console.error("THREE.KeyframeTrack: Out of order keys.",this,a,l,o),t=!1;break}o=l}if(s!==void 0&&Sv(s))for(let a=0,l=s.length;a!==l;++a){let c=s[a];if(isNaN(c)){console.error("THREE.KeyframeTrack: Value is not a valid number.",this,a,c),t=!1;break}}return t}optimize(){let t=this.times.slice(),e=this.values.slice(),i=this.getValueSize(),s=this.getInterpolation()===Aa,r=t.length-1,o=1;for(let a=1;a<r;++a){let l=!1,c=t[a],h=t[a+1];if(c!==h&&(a!==1||c!==t[0]))if(s)l=!0;else{let d=a*i,p=d-i,m=d+i;for(let g=0;g!==i;++g){let _=e[d+g];if(_!==e[p+g]||_!==e[m+g]){l=!0;break}}}if(l){if(a!==o){t[o]=t[a];let d=a*i,p=o*i;for(let m=0;m!==i;++m)e[p+m]=e[d+m]}++o}}if(r>0){t[o]=t[r];for(let a=r*i,l=o*i,c=0;c!==i;++c)e[l+c]=e[a+c];++o}return o!==t.length?(this.times=t.slice(0,o),this.values=e.slice(0,o*i)):(this.times=t,this.values=e),this}clone(){let t=this.times.slice(),e=this.values.slice(),i=this.constructor,s=new i(this.name,t,e);return s.createInterpolant=this.createInterpolant,s}};tn.prototype.TimeBufferType=Float32Array;tn.prototype.ValueBufferType=Float32Array;tn.prototype.DefaultInterpolation=Gl;var xi=class extends tn{constructor(t,e,i){super(t,e,i)}};xi.prototype.ValueTypeName="bool";xi.prototype.ValueBufferType=Array;xi.prototype.DefaultInterpolation=So;xi.prototype.InterpolantFactoryMethodLinear=void 0;xi.prototype.InterpolantFactoryMethodSmooth=void 0;var dc=class extends tn{};dc.prototype.ValueTypeName="color";var pc=class extends tn{};pc.prototype.ValueTypeName="number";var mc=class extends gs{constructor(t,e,i,s){super(t,e,i,s)}interpolate_(t,e,i,s){let r=this.resultBuffer,o=this.sampleValues,a=this.valueSize,l=(i-e)/(s-e),c=t*a;for(let h=c+a;c!==h;c+=4)Yn.slerpFlat(r,0,o,c-a,o,c,l);return r}},Xo=class extends tn{InterpolantFactoryMethodLinear(t){return new mc(this.times,this.values,this.getValueSize(),t)}};Xo.prototype.ValueTypeName="quaternion";Xo.prototype.InterpolantFactoryMethodSmooth=void 0;var _i=class extends tn{constructor(t,e,i){super(t,e,i)}};_i.prototype.ValueTypeName="string";_i.prototype.ValueBufferType=Array;_i.prototype.DefaultInterpolation=So;_i.prototype.InterpolantFactoryMethodLinear=void 0;_i.prototype.InterpolantFactoryMethodSmooth=void 0;var gc=class extends tn{};gc.prototype.ValueTypeName="vector";var xc=class{constructor(t,e,i){let s=this,r=!1,o=0,a=0,l,c=[];this.onStart=void 0,this.onLoad=t,this.onProgress=e,this.onError=i,this.itemStart=function(h){a++,r===!1&&s.onStart!==void 0&&s.onStart(h,o,a),r=!0},this.itemEnd=function(h){o++,s.onProgress!==void 0&&s.onProgress(h,o,a),o===a&&(r=!1,s.onLoad!==void 0&&s.onLoad())},this.itemError=function(h){s.onError!==void 0&&s.onError(h)},this.resolveURL=function(h){return l?l(h):h},this.setURLModifier=function(h){return l=h,this},this.addHandler=function(h,d){return c.push(h,d),this},this.removeHandler=function(h){let d=c.indexOf(h);return d!==-1&&c.splice(d,2),this},this.getHandler=function(h){for(let d=0,p=c.length;d<p;d+=2){let m=c[d],g=c[d+1];if(m.global&&(m.lastIndex=0),m.test(h))return g}return null}}},bv=new xc,_c=class{constructor(t){this.manager=t!==void 0?t:bv,this.crossOrigin="anonymous",this.withCredentials=!1,this.path="",this.resourcePath="",this.requestHeader={}}load(){}loadAsync(t,e){let i=this;return new Promise(function(s,r){i.load(t,s,e,r)})}parse(){}setCrossOrigin(t){return this.crossOrigin=t,this}setWithCredentials(t){return this.withCredentials=t,this}setPath(t){return this.path=t,this}setResourcePath(t){return this.resourcePath=t,this}setRequestHeader(t){return this.requestHeader=t,this}};_c.DEFAULT_MATERIAL_NAME="__DEFAULT";var qo=class{constructor(t=!0){this.autoStart=t,this.startTime=0,this.oldTime=0,this.elapsedTime=0,this.running=!1}start(){this.startTime=Uf(),this.oldTime=this.startTime,this.elapsedTime=0,this.running=!0}stop(){this.getElapsedTime(),this.running=!1,this.autoStart=!1}getElapsedTime(){return this.getDelta(),this.elapsedTime}getDelta(){let t=0;if(this.autoStart&&!this.running)return this.start(),0;if(this.running){let e=Uf();t=(e-this.oldTime)/1e3,this.oldTime=e,this.elapsedTime+=t}return t}};function Uf(){return performance.now()}var Rc="\\[\\]\\.:\\/",Ev=new RegExp("["+Rc+"]","g"),Pc="[^"+Rc+"]",wv="[^"+Rc.replace("\\.","")+"]",Tv=/((?:WC+[\/:])*)/.source.replace("WC",Pc),Av=/(WCOD+)?/.source.replace("WCOD",wv),Cv=/(?:\.(WC+)(?:\[(.+)\])?)?/.source.replace("WC",Pc),Rv=/\.(WC+)(?:\[(.+)\])?/.source.replace("WC",Pc),Pv=new RegExp("^"+Tv+Av+Cv+Rv+"$"),Iv=["material","materials","bones","map"],vc=class{constructor(t,e,i){let s=i||ne.parseTrackName(e);this._targetGroup=t,this._bindings=t.subscribe_(e,s)}getValue(t,e){this.bind();let i=this._targetGroup.nCachedObjects_,s=this._bindings[i];s!==void 0&&s.getValue(t,e)}setValue(t,e){let i=this._bindings;for(let s=this._targetGroup.nCachedObjects_,r=i.length;s!==r;++s)i[s].setValue(t,e)}bind(){let t=this._bindings;for(let e=this._targetGroup.nCachedObjects_,i=t.length;e!==i;++e)t[e].bind()}unbind(){let t=this._bindings;for(let e=this._targetGroup.nCachedObjects_,i=t.length;e!==i;++e)t[e].unbind()}},ne=class n{constructor(t,e,i){this.path=e,this.parsedPath=i||n.parseTrackName(e),this.node=n.findNode(t,this.parsedPath.nodeName),this.rootNode=t,this.getValue=this._getValue_unbound,this.setValue=this._setValue_unbound}static create(t,e,i){return t&&t.isAnimationObjectGroup?new n.Composite(t,e,i):new n(t,e,i)}static sanitizeNodeName(t){return t.replace(/\s/g,"_").replace(Ev,"")}static parseTrackName(t){let e=Pv.exec(t);if(e===null)throw new Error("PropertyBinding: Cannot parse trackName: "+t);let i={nodeName:e[2],objectName:e[3],objectIndex:e[4],propertyName:e[5],propertyIndex:e[6]},s=i.nodeName&&i.nodeName.lastIndexOf(".");if(s!==void 0&&s!==-1){let r=i.nodeName.substring(s+1);Iv.indexOf(r)!==-1&&(i.nodeName=i.nodeName.substring(0,s),i.objectName=r)}if(i.propertyName===null||i.propertyName.length===0)throw new Error("PropertyBinding: can not parse propertyName from trackName: "+t);return i}static findNode(t,e){if(e===void 0||e===""||e==="."||e===-1||e===t.name||e===t.uuid)return t;if(t.skeleton){let i=t.skeleton.getBoneByName(e);if(i!==void 0)return i}if(t.children){let i=function(r){for(let o=0;o<r.length;o++){let a=r[o];if(a.name===e||a.uuid===e)return a;let l=i(a.children);if(l)return l}return null},s=i(t.children);if(s)return s}return null}_getValue_unavailable(){}_setValue_unavailable(){}_getValue_direct(t,e){t[e]=this.targetObject[this.propertyName]}_getValue_array(t,e){let i=this.resolvedProperty;for(let s=0,r=i.length;s!==r;++s)t[e++]=i[s]}_getValue_arrayElement(t,e){t[e]=this.resolvedProperty[this.propertyIndex]}_getValue_toArray(t,e){this.resolvedProperty.toArray(t,e)}_setValue_direct(t,e){this.targetObject[this.propertyName]=t[e]}_setValue_direct_setNeedsUpdate(t,e){this.targetObject[this.propertyName]=t[e],this.targetObject.needsUpdate=!0}_setValue_direct_setMatrixWorldNeedsUpdate(t,e){this.targetObject[this.propertyName]=t[e],this.targetObject.matrixWorldNeedsUpdate=!0}_setValue_array(t,e){let i=this.resolvedProperty;for(let s=0,r=i.length;s!==r;++s)i[s]=t[e++]}_setValue_array_setNeedsUpdate(t,e){let i=this.resolvedProperty;for(let s=0,r=i.length;s!==r;++s)i[s]=t[e++];this.targetObject.needsUpdate=!0}_setValue_array_setMatrixWorldNeedsUpdate(t,e){let i=this.resolvedProperty;for(let s=0,r=i.length;s!==r;++s)i[s]=t[e++];this.targetObject.matrixWorldNeedsUpdate=!0}_setValue_arrayElement(t,e){this.resolvedProperty[this.propertyIndex]=t[e]}_setValue_arrayElement_setNeedsUpdate(t,e){this.resolvedProperty[this.propertyIndex]=t[e],this.targetObject.needsUpdate=!0}_setValue_arrayElement_setMatrixWorldNeedsUpdate(t,e){this.resolvedProperty[this.propertyIndex]=t[e],this.targetObject.matrixWorldNeedsUpdate=!0}_setValue_fromArray(t,e){this.resolvedProperty.fromArray(t,e)}_setValue_fromArray_setNeedsUpdate(t,e){this.resolvedProperty.fromArray(t,e),this.targetObject.needsUpdate=!0}_setValue_fromArray_setMatrixWorldNeedsUpdate(t,e){this.resolvedProperty.fromArray(t,e),this.targetObject.matrixWorldNeedsUpdate=!0}_getValue_unbound(t,e){this.bind(),this.getValue(t,e)}_setValue_unbound(t,e){this.bind(),this.setValue(t,e)}bind(){let t=this.node,e=this.parsedPath,i=e.objectName,s=e.propertyName,r=e.propertyIndex;if(t||(t=n.findNode(this.rootNode,e.nodeName),this.node=t),this.getValue=this._getValue_unavailable,this.setValue=this._setValue_unavailable,!t){console.warn("THREE.PropertyBinding: No target node found for track: "+this.path+".");return}if(i){let c=e.objectIndex;switch(i){case"materials":if(!t.material){console.error("THREE.PropertyBinding: Can not bind to material as node does not have a material.",this);return}if(!t.material.materials){console.error("THREE.PropertyBinding: Can not bind to material.materials as node.material does not have a materials array.",this);return}t=t.material.materials;break;case"bones":if(!t.skeleton){console.error("THREE.PropertyBinding: Can not bind to bones as node does not have a skeleton.",this);return}t=t.skeleton.bones;for(let h=0;h<t.length;h++)if(t[h].name===c){c=h;break}break;case"map":if("map"in t){t=t.map;break}if(!t.material){console.error("THREE.PropertyBinding: Can not bind to material as node does not have a material.",this);return}if(!t.material.map){console.error("THREE.PropertyBinding: Can not bind to material.map as node.material does not have a map.",this);return}t=t.material.map;break;default:if(t[i]===void 0){console.error("THREE.PropertyBinding: Can not bind to objectName of node undefined.",this);return}t=t[i]}if(c!==void 0){if(t[c]===void 0){console.error("THREE.PropertyBinding: Trying to bind to objectIndex of objectName, but is undefined.",this,t);return}t=t[c]}}let o=t[s];if(o===void 0){let c=e.nodeName;console.error("THREE.PropertyBinding: Trying to update property for track: "+c+"."+s+" but it wasn't found.",t);return}let a=this.Versioning.None;this.targetObject=t,t.needsUpdate!==void 0?a=this.Versioning.NeedsUpdate:t.matrixWorldNeedsUpdate!==void 0&&(a=this.Versioning.MatrixWorldNeedsUpdate);let l=this.BindingType.Direct;if(r!==void 0){if(s==="morphTargetInfluences"){if(!t.geometry){console.error("THREE.PropertyBinding: Can not bind to morphTargetInfluences because node does not have a geometry.",this);return}if(!t.geometry.morphAttributes){console.error("THREE.PropertyBinding: Can not bind to morphTargetInfluences because node does not have a geometry.morphAttributes.",this);return}t.morphTargetDictionary[r]!==void 0&&(r=t.morphTargetDictionary[r])}l=this.BindingType.ArrayElement,this.resolvedProperty=o,this.propertyIndex=r}else o.fromArray!==void 0&&o.toArray!==void 0?(l=this.BindingType.HasFromToArray,this.resolvedProperty=o):Array.isArray(o)?(l=this.BindingType.EntireArray,this.resolvedProperty=o):this.propertyName=s;this.getValue=this.GetterByBindingType[l],this.setValue=this.SetterByBindingTypeAndVersioning[l][a]}unbind(){this.node=null,this.getValue=this._getValue_unbound,this.setValue=this._setValue_unbound}};ne.Composite=vc;ne.prototype.BindingType={Direct:0,EntireArray:1,ArrayElement:2,HasFromToArray:3};ne.prototype.Versioning={None:0,NeedsUpdate:1,MatrixWorldNeedsUpdate:2};ne.prototype.GetterByBindingType=[ne.prototype._getValue_direct,ne.prototype._getValue_array,ne.prototype._getValue_arrayElement,ne.prototype._getValue_toArray];ne.prototype.SetterByBindingTypeAndVersioning=[[ne.prototype._setValue_direct,ne.prototype._setValue_direct_setNeedsUpdate,ne.prototype._setValue_direct_setMatrixWorldNeedsUpdate],[ne.prototype._setValue_array,ne.prototype._setValue_array_setNeedsUpdate,ne.prototype._setValue_array_setMatrixWorldNeedsUpdate],[ne.prototype._setValue_arrayElement,ne.prototype._setValue_arrayElement_setNeedsUpdate,ne.prototype._setValue_arrayElement_setMatrixWorldNeedsUpdate],[ne.prototype._setValue_fromArray,ne.prototype._setValue_fromArray_setNeedsUpdate,ne.prototype._setValue_fromArray_setMatrixWorldNeedsUpdate]];var LC=new Float32Array(1);typeof __THREE_DEVTOOLS__!="undefined"&&__THREE_DEVTOOLS__.dispatchEvent(new CustomEvent("register",{detail:{revision:yc}}));typeof window!="undefined"&&(window.__THREE__?console.warn("WARNING: Multiple instances of Three.js being imported."):window.__THREE__=yc);var se=[.16,1,.3,1];function Dv(){let n=document.getElementById("bg-canvas");if(n)try{Uv(n)}catch(t){console.warn("Fondo 3D no disponible (WebGL no soportado):",t)}}function Uv(n){let t=new Vo({canvas:n,antialias:!0,alpha:!0});t.setPixelRatio(Math.min(window.devicePixelRatio,2)),t.setSize(window.innerWidth,window.innerHeight);let e=new zo,i=new Me(60,window.innerWidth/window.innerHeight,.1,100);i.position.z=22;let s=document.body.classList.contains("theme-invert"),r=document.body.classList.contains("theme-industrial"),o=document.body.classList.contains("theme-production"),a=document.body.classList.contains("theme-warehouse"),l=document.body.classList.contains("theme-sur"),c=document.body.classList.contains("theme-quality"),h=document.body.classList.contains("theme-hseq"),d=document.body.classList.contains("theme-operaciones"),p=150,m={x:30,y:18,z:15},g=7.4,_=p*6,u=new Float32Array(p*3),f=new Float32Array(p*3);for(let et=0;et<p;et++)u[et*3]=(Math.random()-.5)*m.x*2,u[et*3+1]=(Math.random()-.5)*m.y*2,u[et*3+2]=(Math.random()-.5)*m.z*2,f[et*3]=(Math.random()-.5)*.02,f[et*3+1]=(Math.random()-.5)*.02,f[et*3+2]=(Math.random()-.5)*.012;let b=new Be;b.setAttribute("position",new Se(u,3));let S=new ms({color:d?s?16735370:13962832:l?s?16739179:15680580:r?s?16747069:16486972:o?s?11766015:11032055:a?s?7312836:5997740:c?s?10718355:6701131:h?s?7840649:1861178:s?16054525:3111935,size:.36,transparent:!0,opacity:.9,sizeAttenuation:!0}),E=new Ws(b,S);e.add(E);let C=new Float32Array(_*2*3),A=new Be;A.setAttribute("position",new Se(C,3)),A.setDrawRange(0,0);let T=new Gs({color:d?s?16756934:15754382:l?s?16757683:16281969:r?s?16763289:16628340:o?s?14271231:12616956:a?s?11584734:9414857:c?s?12233134:8742511:h?s?10074791:4818273:s?14412027:6271999,transparent:!0,opacity:.5}),P=new Go(A,T);e.add(P);let G=0;function x(){let et=0;for(let at=0;at<p&&et<_;at++){let Et=u[at*3],It=u[at*3+1],R=u[at*3+2];for(let ie=at+1;ie<p&&et<_;ie++){let Ut=u[ie*3],Ot=u[ie*3+1],wt=u[ie*3+2],$t=Et-Ut,Tt=It-Ot,w=R-wt;if($t*$t+Tt*Tt+w*w<g*g){let v=et*6;C[v]=Et,C[v+1]=It,C[v+2]=R,C[v+3]=Ut,C[v+4]=Ot,C[v+5]=wt,et++}}}A.attributes.position.needsUpdate=!0,A.setDrawRange(0,et*2),G=et}x();let y=b.clone(),V=new ms({color:d?s?16763352:16501208:l?s?16761538:16698058:r?s?16756838:16701354:o?s?13214719:14732282:a?s?9414857:12110044:c?s?13747913:14735835:h?s?12308932:13820120:12574975,size:.2,transparent:!0,opacity:.45}),U=new Ws(y,V);U.position.z=-14,e.add(U);let H=6,$=.6,k=1.3,Z=.3,z=2.6,it=new Wo(.4,16),ot=[];for(let et=0;et<H;et++){let at=new ds({color:d?s?16769258:12061251:l?s?16704226:14427686:r?s?16772565:15357964:o?s?15984895:9647082:a?s?14739696:2902638:c?s?15789293:4664629:h?s?15266027:1195299:15398655,transparent:!0,opacity:0,side:Je,blending:Mo,depthWrite:!1}),Et=new Fe(it,at);e.add(Et),ot.push({mesh:Et,material:at,start:new F,end:new F,progress:0,duration:1,wait:Math.random()*z,traveling:!1})}function pt(et){let Et=Math.floor(Math.random()*G)*6;et.start.set(C[Et],C[Et+1],C[Et+2]),et.end.set(C[Et+3],C[Et+4],C[Et+5]),et.progress=0,et.duration=$+Math.random()*(k-$),et.traveling=!0}let Bt=0,Vt=0;window.addEventListener("pointermove",et=>{Bt=(et.clientX/window.innerWidth-.5)*2,Vt=(et.clientY/window.innerHeight-.5)*2}),window.addEventListener("resize",()=>{i.aspect=window.innerWidth/window.innerHeight,i.updateProjectionMatrix(),t.setSize(window.innerWidth,window.innerHeight)});let X=window.matchMedia("(prefers-reduced-motion: reduce)").matches,J,ht=0,rt=new qo;function bt(){J=requestAnimationFrame(bt),ht++;let et=rt.getDelta();for(let at=0;at<p;at++){let Et=at*3,It=at*3+1,R=at*3+2,ie=u[Et]+f[Et],Ut=u[It]+f[It],Ot=u[R]+f[R];(ie>m.x||ie<-m.x)&&(f[Et]*=-1),(Ut>m.y||Ut<-m.y)&&(f[It]*=-1),(Ot>m.z||Ot<-m.z)&&(f[R]*=-1),u[Et]=ie,u[It]=Ut,u[R]=Ot}b.attributes.position.needsUpdate=!0,ht%12===0&&x(),U.rotation.y+=.0015;for(let at of ot){if(!at.traveling){at.wait-=et,at.wait<=0&&G>0&&pt(at);continue}if(at.progress+=et/at.duration,at.progress>=1){at.traveling=!1,at.wait=Z+Math.random()*(z-Z),at.material.opacity=0;continue}at.mesh.position.lerpVectors(at.start,at.end,at.progress),at.mesh.lookAt(i.position);let Et=Math.min(1,at.progress*6),It=Math.min(1,(1-at.progress)*6),R=Et*It;at.material.opacity=R*.95,at.mesh.scale.setScalar(.7+R*.7)}i.position.x+=(Bt*2-i.position.x)*.03,i.position.y+=(-Vt*1.2-i.position.y)*.03,i.lookAt(0,0,0),t.render(e,i)}X?t.render(e,i):bt(),document.addEventListener("visibilitychange",()=>{document.hidden?cancelAnimationFrame(J):X||bt()})}function Nv(){Dt(".brand",{opacity:[0,1],transform:["translateY(-8px)","translateY(0px)"]},{duration:.6,ease:se}),Dt(".auth-card",{opacity:[0,1],transform:["translateY(18px) scale(0.98)","translateY(0px) scale(1)"]},{duration:.7,delay:.15,ease:se});let n=Array.from(document.querySelectorAll(".field, .submit-row, .auth-foot, .form-section-title, .step-indicator")).filter(t=>!t.closest(".form-step--hidden"));Dt(n,{opacity:[0,1],transform:["translateY(14px)","translateY(0px)"]},{duration:.5,delay:ei(.08,{startDelay:.35}),ease:se}),Dt(".status-line",{opacity:[0,1]},{duration:.5,delay:.9})}function Fv(){let n=document.querySelector(".btn-primary");n&&(n.addEventListener("mouseenter",()=>{Dt(n,{transform:["scale(1)","scale(1.015)"]},{duration:.2,ease:"easeOut"})}),n.addEventListener("mouseleave",()=>{Dt(n,{transform:["scale(1.015)","scale(1)"]},{duration:.2,ease:"easeOut"})})),document.querySelectorAll(".field input").forEach(t=>{t.addEventListener("focus",()=>{Dt(t,{transform:["scale(1)","scale(1.01)"]},{duration:.15,ease:"easeOut"})}),t.addEventListener("blur",()=>{Dt(t,{transform:["scale(1.01)","scale(1)"]},{duration:.15,ease:"easeOut"})})})}var Ic=class{constructor(t){this.options=[];this.isOpen=!1;this.activeIndex=-1;this.highlightReady=!1;var s,r,o,a;this.select=t,this.field=t.closest(".field"),this.placeholder=(o=(r=(s=t.querySelector('option[value=""]'))==null?void 0:s.textContent)==null?void 0:r.trim())!=null?o:"Selecciona",this.wrapper=document.createElement("div"),this.wrapper.className="select-shell",this.trigger=document.createElement("button"),this.trigger.type="button",this.trigger.className="select-trigger",this.trigger.setAttribute("aria-haspopup","listbox"),this.trigger.setAttribute("aria-expanded","false"),t.id&&(this.trigger.id=`${t.id}__trigger`),this.triggerLabel=document.createElement("span"),this.triggerLabel.className="select-trigger-label",this.triggerLabel.textContent=this.placeholder,this.trigger.appendChild(this.triggerLabel);let e=document.createElement("span");e.className="select-chevron",e.innerHTML='<svg xmlns="http://www.w3.org/2000/svg" width="11" height="7" viewBox="0 0 11 7" fill="none"><path d="M1 1L5.5 5.5L10 1" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>',this.trigger.appendChild(e),this.panel=document.createElement("div"),this.panel.className="select-panel",this.panel.setAttribute("role","listbox"),this.highlight=document.createElement("div"),this.highlight.className="select-highlight",this.list=document.createElement("div"),this.list.className="select-list",this.list.appendChild(this.highlight),t.querySelectorAll("option").forEach(l=>{var h,d;if(l.value==="")return;let c=document.createElement("div");c.className="select-option",c.textContent=(d=(h=l.textContent)==null?void 0:h.trim())!=null?d:l.value,c.dataset.value=l.value,c.setAttribute("role","option"),this.list.appendChild(c),this.options.push(c),c.addEventListener("mouseenter",()=>this.setActive(this.options.indexOf(c),!1)),c.addEventListener("click",()=>this.commit(this.options.indexOf(c)))}),this.panel.appendChild(this.list),this.wrapper.appendChild(this.trigger),this.wrapper.appendChild(this.panel),(a=t.parentElement)==null||a.insertBefore(this.wrapper,t),this.wrapper.appendChild(t),t.classList.add("select-native"),t.tabIndex=-1,t.removeAttribute("required");let i=t.id?document.querySelector(`label[for="${t.id}"]`):null;i&&(i.htmlFor=this.trigger.id),this.trigger.addEventListener("click",()=>this.toggle()),this.trigger.addEventListener("keydown",l=>this.onTriggerKeydown(l)),document.addEventListener("click",l=>{this.wrapper.contains(l.target)||this.close()})}get hasValue(){return this.select.value!==""}clearError(){this.trigger.classList.remove("select-trigger--error")}showError(){this.trigger.classList.add("select-trigger--error"),Dt(this.trigger,{transform:["translateX(0px)","translateX(-6px)","translateX(6px)","translateX(-4px)","translateX(4px)","translateX(0px)"]},{duration:.4,ease:"easeOut"})}toggle(){this.isOpen?this.close():this.open()}open(){var e;if(this.isOpen)return;this.isOpen=!0,this.highlightReady=!1,this.clearError(),this.trigger.setAttribute("aria-expanded","true"),this.panel.classList.add("select-panel--open"),(e=this.field)==null||e.classList.add("field--select-open");let t=this.options.findIndex(i=>i.dataset.value===this.select.value);this.setActive(t>=0?t:0,!0),Dt(this.panel,{opacity:[0,1],transform:["translateY(-6px) scale(0.97)","translateY(0px) scale(1)"]},{duration:.22,ease:se}),Dt(this.options,{opacity:[0,1],transform:["translateY(-4px)","translateY(0px)"]},{duration:.22,delay:ei(.025),ease:se})}close(){this.isOpen&&(this.isOpen=!1,this.trigger.setAttribute("aria-expanded","false"),Dt(this.panel,{opacity:[1,0],transform:["translateY(0px) scale(1)","translateY(-6px) scale(0.97)"]},{duration:.16,ease:"easeIn"}).then(()=>{var t;this.isOpen||(this.panel.classList.remove("select-panel--open"),(t=this.field)==null||t.classList.remove("field--select-open"))}))}setActive(t,e){if(t<0||t>=this.options.length)return;this.activeIndex=t,this.options.forEach((o,a)=>o.classList.toggle("select-option--active",a===t));let i=this.options[t],s=i.offsetTop,r=i.offsetHeight;!this.highlightReady||e?(this.highlight.style.top=`${s}px`,this.highlight.style.height=`${r}px`,this.highlight.style.opacity="1",this.highlightReady=!0):Dt(this.highlight,{top:`${s}px`,height:`${r}px`},{duration:.2,ease:se})}commit(t){var s;let e=this.options[t];if(!e)return;let i=(s=e.dataset.value)!=null?s:"";this.select.value=i,this.select.dispatchEvent(new Event("change",{bubbles:!0})),this.triggerLabel.textContent=e.textContent,this.options.forEach((r,o)=>r.classList.toggle("select-option--selected",o===t)),this.clearError(),this.close(),this.trigger.focus()}onTriggerKeydown(t){switch(t.key){case"ArrowDown":t.preventDefault(),this.isOpen?this.setActive(Math.min(this.activeIndex+1,this.options.length-1),!1):this.open();break;case"ArrowUp":t.preventDefault(),this.isOpen?this.setActive(Math.max(this.activeIndex-1,0),!1):this.open();break;case"Enter":case" ":t.preventDefault(),this.isOpen?this.commit(this.activeIndex):this.open();break;case"Escape":this.close();break;case"Home":this.isOpen&&(t.preventDefault(),this.setActive(0,!1));break;case"End":this.isOpen&&(t.preventDefault(),this.setActive(this.options.length-1,!1));break}}};function Ov(){let n=document.querySelectorAll("#cargo, #campo_sede, #campo_rol, #campo_Area");return Array.from(n).map(t=>new Ic(t))}function Bv(){let n=document.querySelector(".auth-card"),t=document.querySelector(".brand"),e=document.querySelector(".status-line");document.body.style.overflow="hidden";let i=[];return n&&i.push(Dt(n,{opacity:[1,0],transform:["translateY(0vh) scale(1)","translateY(120vh) scale(0.94)"]},{duration:.6,ease:se})),e&&i.push(Dt(e,{opacity:[1,0]},{duration:.25,ease:"easeIn"})),t&&i.push(Dt(t,{transform:["scale(1)","scale(2.8)"]},{duration:.85,delay:.2,ease:se})),Promise.all(i).then(()=>{})}function Vv(n,t,e){var r;let i=(r=t.textContent)!=null?r:"Iniciar sesi\xF3n";function s(o){t.disabled=!1,t.textContent=i;let a=window.Swal;a&&a.fire({icon:"error",title:"Credenciales incorrectas",text:o,background:"#ffffff",color:"#0b1b33",confirmButtonColor:"#2563eb"})}n.addEventListener("submit",o=>{var l;o.preventDefault();let a=e.filter(c=>!c.hasValue);if(a.length>0){a.forEach(c=>c.showError());return}t.disabled=!0,t.textContent=(l=t.dataset.loadingText)!=null?l:"Verificando\u2026",fetch(window.location.pathname,{method:"POST",headers:{"X-Requested-With":"fetch"},body:new FormData(n)}).then(c=>c.json()).then(c=>{if(c.status==="success"&&c.redirect){Bv().then(()=>{window.location.href=c.redirect});return}s(c.message||"Por favor, verifica los datos.")}).catch(()=>{s("Ocurri\xF3 un error de conexi\xF3n. Intenta de nuevo.")})})}function zv(n){var i;let t=document.querySelector(".auth-form"),e=(i=t==null?void 0:t.querySelector('button[type="submit"]'))!=null?i:null;if(!(!t||!e)){if(t.classList.contains("auth-form--steps")){t.addEventListener("submit",s=>{var o;let r=n.filter(a=>!a.hasValue);if(r.length>0){s.preventDefault(),r.forEach(a=>a.showError());return}e.disabled=!0,e.textContent=(o=e.dataset.loadingText)!=null?o:"Verificando\u2026"});return}Vv(t,e,n)}}function kv(n){var _;let t=document.querySelector(".auth-form--steps");if(!t)return;let e=t,i=Array.from(e.querySelectorAll(".form-step")),s=Array.from(e.querySelectorAll("[data-step-circle]")),r=Array.from(e.querySelectorAll("[data-step-label]")),o=Array.from(e.querySelectorAll(".step-track-fill")),a=1;function l(u){s.forEach(f=>{let b=Number(f.dataset.stepCircle);f.classList.toggle("step-circle--active",b===u),f.classList.toggle("step-circle--done",b<u)}),r.forEach(f=>{let b=Number(f.dataset.stepLabel);f.classList.toggle("step-label--active",b<=u)}),o.forEach((f,b)=>{Dt(f,{width:u>b+1?"100%":"0%"},{duration:.4,ease:se})})}function c(u){u.classList.remove("form-step--hidden"),Dt(u,{opacity:[0,1],transform:["translateY(10px)","translateY(0px)"]},{duration:.4,ease:se}),Dt(u.querySelectorAll(".field, .submit-row, .form-section-title"),{opacity:[0,1],transform:["translateY(14px)","translateY(0px)"]},{duration:.45,delay:ei(.07,{startDelay:.12}),ease:se})}function h(u){let f=i[a-1],b=i[u-1];u===a||!f||!b||(Dt(f,{opacity:[1,0]},{duration:.18,ease:"easeIn"}).then(()=>{f.classList.add("form-step--hidden")}),c(b),l(u),a=u)}function d(u){u&&(u.classList.add("field-input--error"),Dt(u,{transform:["translateX(0px)","translateX(-6px)","translateX(6px)","translateX(-4px)","translateX(4px)","translateX(0px)"]},{duration:.4,ease:"easeOut"}))}function p(){let u=e.querySelector("#campo_nombre"),f=e.querySelector("#campo_cedula"),b=e.querySelector("#campo_cedula2"),S=!0;return[u,f,b].forEach(E=>{E==null||E.classList.remove("field-input--error"),E!=null&&E.value.trim()||(S=!1,d(E))}),f!=null&&f.value.trim()&&(b!=null&&b.value.trim())&&f.value.trim()!==b.value.trim()&&(S=!1,d(b)),S}function m(){let u=n.filter(f=>!f.hasValue);return u.forEach(f=>f.showError()),u.length===0}let g={1:p,2:m};e.querySelectorAll("[data-goto]").forEach(u=>{let f=Number(u.dataset.goto);u.addEventListener("click",()=>{var b,S;f>a&&!((S=(b=g[a])==null?void 0:b.call(g))==null||S)||h(f)})}),(_=e.querySelector("#campo_cedula2"))==null||_.addEventListener("input",u=>{u.currentTarget.classList.remove("field-input--error")})}function Hv(){if(new URLSearchParams(window.location.search).get("motivo")!=="sesion")return;let t=window.Swal;t&&t.fire({icon:"warning",title:"Sesi\xF3n expirada",text:"Por favor, inicia sesi\xF3n nuevamente.",background:"#ffffff",color:"#0b1b33",confirmButtonColor:"#2563eb"})}function Gv(){document.querySelectorAll("[data-reveal]").forEach(n=>{wa(n,()=>{Dt(n,{opacity:[0,1],transform:["translateY(24px)","translateY(0px)"]},{duration:.6,ease:se})})})}var Wv="radial-gradient(circle, #0b0e14 0%, #05070b 70%)";function sd(n,t,e,i){return new Promise(s=>{let r=performance.now();function o(a){let l=Math.min(1,(a-r)/e),c=1-Math.pow(1-l,3);i(n+(t-n)*c),l<1?requestAnimationFrame(o):s()}requestAnimationFrame(o)})}function rd(){let n=document.querySelector(".gear-wave");return n||(n=document.createElement("div"),n.className="gear-wave",document.body.appendChild(n)),n.style.background=Wv,n}function Zo(n){let t=n.querySelector(".hub-center"),e=(t!=null?t:n).getBoundingClientRect();return{x:e.left+e.width/2,y:e.top+e.height/2}}function od(n,t){return Math.hypot(Math.max(n,window.innerWidth-n),Math.max(t,window.innerHeight-t))}function Xv(n,t){let{x:e,y:i}=Zo(n),s=rd();s.style.clipPath=`circle(0px at ${e}px ${i}px)`;let r=Array.from(document.querySelectorAll(".menu-exit"));r.length&&Dt(r,{opacity:[1,0]},{duration:.35,ease:"easeIn"}),sd(0,od(e,i),700,o=>{s.style.clipPath=`circle(${o}px at ${e}px ${i}px)`}).then(()=>{window.location.href=t})}function qv(){let n=document.querySelector(".hub-center-anchor[data-wave-entrance]"),t=document.querySelector(".hub");if(!n||!t)return Promise.resolve();let{x:e,y:i}=Zo(t),s=od(e,i),r=rd();return r.style.clipPath=`circle(${s}px at ${e}px ${i}px)`,sd(s,0,750,o=>{r.style.clipPath=`circle(${o}px at ${e}px ${i}px)`}).then(()=>{r.remove()})}function ad(n,t){let e=n.getBoundingClientRect(),i=e.left+e.width/2,s=e.top+e.height/2;return{dx:t.x-i,dy:t.y-s}}function Yv(n,t){let e=Zo(n),i=Array.from(n.querySelectorAll(".hub-node")),s=Array.from(document.querySelectorAll(".menu-exit")),r=i.map(o=>{let{dx:a,dy:l}=ad(o,e);return Dt(o,{x:[0,a],y:[0,l],scale:[1,0],opacity:[1,0]},{duration:.5,ease:"easeIn"})});s.length&&r.push(Dt(s,{opacity:[1,0]},{duration:.35,ease:"easeIn"})),Promise.all(r).then(()=>{window.location.href=t})}function Kv(){let n=document.querySelector(".hub[data-unroll-entrance]");if(!n)return;let t=Zo(n);Array.from(n.querySelectorAll(".hub-node")).forEach((i,s)=>{let{dx:r,dy:o}=ad(i,t);i.style.opacity="0",i.style.transform=`translate(${r}px, ${o}px) scale(0)`,Dt(i,{x:[r,0],y:[o,0],scale:[0,1],opacity:[0,1]},{duration:.55,delay:.2+s*.05,ease:se})})}var vi={y:60.5,scale:1.9},$v=550;function Zv(n){let t=n.querySelector(".area-symbol-wheat"),e=n.querySelector(".area-symbol-check"),i=n.querySelector(".area-check-draw");if(!t||!e||!i||n.dataset.areaChecked==="1")return Promise.resolve();n.dataset.areaChecked="1";let s=450,r=new Promise(c=>{let h=performance.now();function d(p){let m=Math.min(1,(p-h)/s),g=1-Math.pow(1-m,4),_=vi.y*(1-g),u=vi.scale+(1-vi.scale)*g;t.style.transform=`translateY(${_}px) scale(${u})`,m<1?requestAnimationFrame(d):c()}requestAnimationFrame(d)}),o=150,a=480,l=new Promise(c=>{let h=performance.now()+o;function d(p){let m=Math.max(0,Math.min(1,(p-h)/a)),g=m<.5?4*m*m*m:1-Math.pow(-2*m+2,3)/2;i.setAttribute("stroke-dashoffset",String(1-g)),m<1?requestAnimationFrame(d):c()}requestAnimationFrame(d)}).then(()=>Dt(e,{scale:[1,1.06,1]},{duration:.28,ease:"easeOut"}));return Promise.all([r,l]).then(()=>new Promise(c=>setTimeout(c,180)))}function Jv(n){let t=n.querySelector(".area-symbol-wheat"),e=n.querySelector(".area-check-draw");!t||!e||(delete n.dataset.areaChecked,t.style.transform="",e.setAttribute("stroke-dashoffset","1"))}var ld=[[".area-third-dark",[28,102,58]],[".area-third-light",[61,174,147]]];function id(n){return(n!=null?n:"").trim().split(/\s+/).map(t=>t.split(",").map(Number))}function Qv(n){let t=n.querySelector(".area-leaf"),e=n.querySelector(".area-leaf-glow"),i=n.querySelector(".area-leaf-shine"),s=n.querySelector(".area-leaf-knockout"),r=n.querySelector(".area-third-shield"),o=n.querySelector(".area-leaf-morph");if(!t||!e||!i||n.dataset.areaLeafShown==="1")return Promise.resolve();n.dataset.areaLeafShown="1";let a=id(o==null?void 0:o.dataset.morphFrom),l=id(o==null?void 0:o.dataset.morphTo),c=!!(r&&o&&a.length>2&&a.length===l.length),h=ld.map(([u,f])=>({el:r==null?void 0:r.querySelector(u),rgb:f})),d=n.querySelector(".area-settle-rotator")?350:0,p=1400,m=u=>1-Math.pow(1-u,3),g=u=>u<.5?4*u*u*u:1-Math.pow(-2*u+2,3)/2,_=(u,f,b)=>Math.max(0,Math.min(1,(u-f)/(b-f)));return new Promise(u=>{let f=performance.now();function b(S){let E=S-f-d;if(c){let G=m(_(E,0,200));h.forEach(({el:H,rgb:$})=>{if(!H)return;let[k,Z,z]=$.map(it=>Math.round(it+(255-it)*G));H.style.stroke=`rgb(${k}, ${Z}, ${z})`}),r.style.opacity=String(1-_(E,180,270));let x=m(_(E,100,270)),y=g(_(E,250,750)),V=g(_(E,800,1100));o.style.opacity=String(x*(1-V));let U="";for(let H=0;H<a.length;H++){let $=a[H][0]+(l[H][0]-a[H][0])*y,k=a[H][1]+(l[H][1]-a[H][1])*y;U+=`${H?"L":"M"}${$.toFixed(1)},${k.toFixed(1)}`}o.setAttribute("d",U+"Z")}let C=m(_(E,650,850)),A=g(_(E,850,1200));e.style.opacity=String(C*(1-A)*.85),e.style.transform=`scale(${.94+.08*C})`;let T=m(_(E,c?750:120,c?1100:720));t.style.opacity=String(T),s&&(s.style.opacity=String(T));let P=_(E,950,p);i.setAttribute("x",String(1700+360*g(P))),i.style.opacity=P>0&&P<1?String(Math.sin(Math.PI*P)):"0",E<p?requestAnimationFrame(b):u()}requestAnimationFrame(b)}).then(()=>new Promise(u=>setTimeout(u,150)))}function jv(n){let t=n.querySelector(".area-leaf"),e=n.querySelector(".area-leaf-glow"),i=n.querySelector(".area-leaf-shine");if(!t||!e||!i)return;delete n.dataset.areaLeafShown,t.style.opacity="",e.style.opacity="",e.style.transform="",i.style.opacity="",i.setAttribute("x","1700");let s=n.querySelector(".area-leaf-knockout");s&&(s.style.opacity="");let r=n.querySelector(".area-third-shield");r&&(r.style.opacity="",ld.forEach(([a,l])=>{let c=r.querySelector(a);c&&(c.style.stroke=`rgb(${l.join(", ")})`)}));let o=n.querySelector(".area-leaf-morph");o&&(o.style.opacity="")}function ty(){let n=document.querySelector(".hub");if(!n)return;let t=n.querySelector(".gear-rotator"),e=n.querySelector(".gear-ring"),i=n.querySelector(".hub-center"),s=n.querySelector(".area-symbol"),r=n.querySelector(".area-gear-rotator"),o=n.querySelector(".area-hex-rotator, .area-settle-rotator"),a=Array.from(n.querySelectorAll(".area-counter-rotator")),l=null,c=null,h=Array.from(n.querySelectorAll(".hub-node"));i&&Dt(i,{scale:[1,1.02,1]},{duration:4,repeat:1/0,ease:"easeInOut"});let d=170,p=.35,m=16,g=window.innerWidth/2,_=window.innerHeight/2,u=!1,f=.08,b=0,S=0,E=0,C=null;function A(G,x){return((x-G)%360+540)%360-180}function T(G){if(i){let y=i.getBoundingClientRect(),V=y.left+y.width/2,U=y.top+y.height/2,H=g-V,$=_-U;S=Math.atan2($,H)*(180/Math.PI)}b+=A(b,S)*f;let x=E;if(C){let y=Math.min(1,(G-C.start)/C.duration),V=1-Math.pow(1-y,3);x=C.from+(C.to-C.from)*V,y>=1&&(E=C.to,C=null)}if(t&&(t.style.transform=`rotate(${b+x}deg)`),r&&(r.style.transform=`rotate(${b+x}deg)`),o){let y=b+x;if(l){let V=Math.min(1,(G-l.start)/l.duration),U=1-Math.pow(1-V,3);y=l.from+(l.to-l.from)*U,V>=1&&(c=l.to,l=null)}else c!==null&&(y=c);o.style.transform=`rotate(${y}deg)`,a.forEach(V=>{V.style.transform=`rotate(${-y}deg)`})}requestAnimationFrame(T)}requestAnimationFrame(T),i&&(i.addEventListener("mouseenter",()=>{e==null||e.classList.add("is-hovering"),s==null||s.classList.add("is-hovering")}),i.addEventListener("mouseleave",()=>{e==null||e.classList.remove("is-hovering"),s==null||s.classList.remove("is-hovering")}),i.addEventListener("click",()=>{if(o&&c===null&&!l){let V=b+E,U=Math.ceil(V/360)*360+360;l={start:performance.now(),from:V,to:U,duration:$v}}C={start:performance.now(),from:E,to:E+360,duration:900},e==null||e.classList.remove("is-pulsing"),e==null||e.getBoundingClientRect(),e==null||e.classList.add("is-pulsing");let G=Promise.all([Zv(n),Qv(n)]),x=i.dataset.gearNav;if(!x)return;let y=()=>{G.then(()=>{i.dataset.gearTransition==="retract"?Yv(n,x):Xv(n,x)})};i.dataset.gearCheck==="admin"?fetch("verificar_admin.php").then(V=>V.json()).then(V=>{V.esAdmin&&y()}).catch(()=>{}):y()}));function P(){u=!1,h.forEach(G=>{let x=G.getBoundingClientRect(),y=x.left+x.width/2,V=x.top+x.height/2,U=g-y,H=_-V,$=Math.hypot(U,H);if($<d){let k=1-$/d,Z=Math.max(-m,Math.min(m,U*p*k)),z=Math.max(-m,Math.min(m,H*p*k));Dt(G,{x:Z,y:z,scale:1+k*.05},{duration:.4,ease:se})}else Dt(G,{x:0,y:0,scale:1},{duration:.5,ease:se})})}window.addEventListener("pageshow",G=>{G.persisted&&(l=null,c=null)}),window.addEventListener("pointermove",G=>{g=G.clientX,_=G.clientY,u||(u=!0,requestAnimationFrame(P))}),document.addEventListener("pointerleave",()=>{h.forEach(G=>{Dt(G,{x:0,y:0,scale:1},{duration:.5,ease:se})})})}function ey(){let n=document.querySelector(".hub");if(!n)return;let t=n.querySelector("#progress-arc"),e=n.querySelector("#progress-node"),i=n.querySelector(".gear-teeth"),s=n.querySelector(".gear-chart-hill"),r=Array.from(n.querySelectorAll(".gear-chart-bar")),o=[.34,1.56,.64,1];i&&Dt(i,{opacity:[0,1],scale:[.82,1],rotate:[-24,0]},{duration:.7,ease:se}),t&&Dt(t,{opacity:[0,1]},{duration:.9,delay:.25,ease:se}),e&&Dt(e,{opacity:[0,1],scale:[.3,1]},{duration:.45,delay:1.05,ease:o}),s&&Dt(s,{opacity:[0,1]},{duration:.5,delay:.35,ease:se}),r.length&&Dt(r,{opacity:[0,1],scaleY:[0,1]},{duration:.5,delay:ei(.09,{startDelay:.45}),ease:se});let a=n.querySelector(".area-symbol-helmet"),l=n.querySelector(".area-symbol-gear"),c=n.querySelector(".area-symbol-hub");(a||c)&&Dt([a,c].filter(p=>p!==null),{opacity:[0,1]},{duration:.5,ease:se}),l&&Dt(l,{opacity:[0,1],rotate:[-180,0]},{duration:1.1,delay:.15,ease:se});let h=Array.from(n.querySelectorAll(".area-symbol-hex-light, .area-symbol-hex")),d=n.querySelector(".area-symbol-wheat");h.length&&Dt(h,{opacity:[0,1],scale:[.85,1]},{duration:.6,delay:ei(.12),ease:se}),d&&Dt(d,{opacity:[0,1],y:[vi.y,vi.y],scale:[vi.scale*.7,vi.scale]},{duration:.6,delay:.35,ease:o})}document.addEventListener("DOMContentLoaded",()=>{Nv(),Fv();let n=Ov();zv(n),kv(n),Hv(),Gv(),ty(),ey(),window.addEventListener("pageshow",t=>{let e=document.querySelector(".hub");t.persisted&&e&&(Jv(e),jv(e))}),Kv(),qv().then(Dv)});})();
/*! Bundled license information:

three/build/three.module.js:
  (**
   * @license
   * Copyright 2010-2024 Three.js Authors
   * SPDX-License-Identifier: MIT
   *)
*/
