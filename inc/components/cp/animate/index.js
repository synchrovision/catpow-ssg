import {bez} from 'cp/calc'; 
import {easeInOutQuad} from './ease.jsx';
export * from './ease.jsx';

export const scrollTo=(tgt,dur=500,ease=null)=>{
	const s=parseInt(window.scrollY);
	var d;
	if(isNaN(tgt)){
		if(!(tgt instanceof HTMLElement)){
			tgt=document.querySelector(tgt);
			if(!tgt){return;}
		}
		d=tgt.getBoundingClientRect().top;
	}
	else{
		d=tgt-s;
	}
	animate((p)=>{window.scrollTo(0,s+d*p);},dur,ease);
};
export const animate=(cb,dur=500,ease=null)=>{
	var s=parseInt(performance.now()),c=1/dur,p=0;
	if(ease===null){ease=easeInOutQuad;}
	if(Array.isArray(ease)){
		const ns=ease;
		ns.unshift(0);ns.push(1);
		ease=(p)=>bez(ns,p);
	}
	const tick=(t)=>{
		p=(t-s)*c;
		if(p>1){return cb(1);}
		window.requestAnimationFrame(tick);
		return cb(ease(p));
	}
	window.requestAnimationFrame(tick);
};