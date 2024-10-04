export const ready=(cb)=>document.readyState!=='loading'?cb():document.addEventListener('DOMContentLoaded',cb);

export const scrollspy=function(items,param={}){
	const app={};
	app.param=Object.assign({threshold:[0,.01,.25,.5,1]},param);
	app.observer=new IntersectionObserver((entries)=>{
		entries.forEach((entry)=>{
			if(entry.intersectionRatio>0){entry.target.classList.add('has-visible');}
			if(entry.intersectionRatio>=.25){entry.target.classList.add('has-quarter-visible');}
			if(entry.intersectionRatio>=.5){entry.target.classList.add('has-half-visible');}
			if(entry.intersectionRatio===1){entry.target.classList.add('has-full-visible');}
			entry.target.classList.toggle('is-visible',entry.intersectionRatio>0);
			entry.target.classList.toggle('is-quarter-visible',entry.intersectionRatio>=.25);
			entry.target.classList.toggle('is-half-visible',entry.intersectionRatio>=.5);
			entry.target.classList.toggle('is-full-visible',entry.intersectionRatio===1);
		});
	},app.param);
	items.forEach((item)=>app.observer.observe(item));
	return app;
}
export const observeIntersection=function(items,param={}){
	const app={};
	const map=new WeakMap();
	app.param=Object.assign({threshold:[0,.01,.25,.5,1]},param);
	app.observer=new IntersectionObserver((entries)=>{
		entries.forEach((entry)=>{
			const el=entry.target;
			const r=entry.intersectionRatio;
			const prev=map.has(el)?map.get(el):0;
			map.set(el,r);
			if(r>prev){
				if(prev===0){el.dispatchEvent(new Event('enterIntoView'));}
				if(prev<0.5){
					if(r>0.5){el.dispatchEvent(new Event('enterHalfIntoView'));}
				}
				if(r===1){el.dispatchEvent(new Event('enterFullIntoView'));}
			}
			else{
				if(r===0){el.dispatchEvent(new Event('leaveFullFromView'));}
				else if(prev>0.5){
					if(prev===1){
						el.dispatchEvent(new Event('leaveFromView'));
					}
					if(r<0.5){el.dispatchEvent(new Event('leaveHalfFromView'));}
				}
			}
			
		});
	},app.param);
	if(items.forEach){
		items.forEach((item)=>app.observer.observe(item));
	}
	else{
		app.observer.observe(items);
	}
	return app;
}
export const observeSelector=(selector,callback)=>{
	const observer=new MutationObserver((mutationList)=>{
		mutationList.forEach((mutation)=>{
			if(mutation.type==='childList'){
				mutation.addedNodes.forEach((node)=>{
					if(node instanceof Element){
						node.querySelectorAll(selector).forEach(callback);
					}
				});
			}
		});
	});
	observer.observe(document.body,{childList:true,subtree:true});
	document.querySelectorAll(selector).forEach(callback);
}