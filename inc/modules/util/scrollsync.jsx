import {debounce} from './debounce.jsx';

export const scrollsync=function(el,param={}){
	const app={};
	app.param=Object.assign({nav:false,items:el.children},param);
	if(!app.param.direction){
		app.param.direction=(el.clientHeight<el.scrollHeight)?'y':'x';
	}
	if(!app.param.position){
		const ssa=getComputedStyle(app.param.items[0])['scroll-snap-align'];
		app.param.position={start:0,center:0.5,end:1}[ssa] || 0;
	}
	const k1={x:'left',y:'top'}[app.param.direction];
	const k2={x:'right',y:'bottom'}[app.param.direction];
	const k3={x:'scrollLeft',y:'scrollTop'}[app.param.direction];
	const k4={x:'width',y:'height'}[app.param.direction];
	const k5={x:'scrollWidth',y:'scrollHeight'}[app.param.direction];
	const ps={x:'paddingLeft',y:'paddingTop'}[app.param.direction];
	const pe={x:'paddingRight',y:'paddingBottom'}[app.param.direction];
	if(!app.param.padding){
		const ssa=getComputedStyle(app.param.items[0])['scroll-snap-align'];
		app.param.padding=parseFloat(getComputedStyle(el)['scroll-padding-'+(ssa==='end'?k2:k1)]);
	}
	const updateActiveItem=()=>{
		let i,index=-1;
		const l=app.param.items.length;
		const bnd=el.getBoundingClientRect();
		const c=bnd[k1]+app.param.padding+bnd[k4]*app.param.position;
		for(i=0;i<l;i++){
			const bnd=app.param.items[i].getBoundingClientRect();
			if(bnd[k2]>c){index=i;break;}
		}
		updateItemsClass(app.param.items,index);
		if(app.param.navItemsList){
			app.param.navItemsList.forEach((items)=>updateItemsClass(items,index));
		}
		if(app.param.controls){
			if(app.param.controls.prev){app.param.controls.prev.classList.toggle('is-disabled',index<=0);}
			if(app.param.controls.next){app.param.controls.next.classList.toggle('is-disabled',index>=l-1);}
		}
		app.current=index;
		el.style.setProperty('--scroll-index',index);
	};
	const updateItemsClass=(items,index)=>{
		const l=items.length;
		for(let i=0;i<l;i++){
			const item=items[i];
			const p=i-index;
			item.classList.toggle('is-before',p<0);
			item.classList.toggle('is-prev',p===-1);
			item.classList.toggle('is-current',p===0);
			item.classList.toggle('is-next',p===1);
			item.classList.toggle('is-after',p>0);
			item.style.setProperty('--scroll-p',p);
		}
	};
	const updateNavClass=()=>{
		if(!app.param.nav){return;}
		const bnd=el.getBoundingClientRect();
		const p=app.param.position;
		const ls=bnd[k1]+bnd[k4]*p-el[k3];
		const le=ls+el[k5]-bnd[k4];
		for(let i=0;i<app.param.items.length;i++){
			const bnd=app.param.items[i].getBoundingClientRect();
			app.param.navItemsList.forEach((items)=>{
				items[i].classList.toggle('is-disabled',bnd[k2]<ls || bnd[k1]>le);
			});
		}
	}
	const observer=new ResizeObserver(updateNavClass);
	observer.observe(el);
	app.goto=(index)=>{
		const bnd1=el.getBoundingClientRect();
		const bnd2=app.param.items[index].getBoundingClientRect();
		const p=app.param.position;
		el.scrollTo({
			[k1]:el[k3]-app.param.padding+(bnd2[k1]+bnd2[k4]*p)-(bnd1[k1]+bnd1[k4]*p)
		});
		updateActiveItem();
	};
	app.prev=()=>app.goto(Math.max(0,app.current-1));
	app.next=()=>app.goto(Math.min(items.length-1,app.current+1));
	const registerAsNav=(items)=>{
		const l=items.length;
		for(let i=0;i<l;i++){
			items[i].addEventListener('click',()=>app.goto(i));
		}
	}
	const getNavtItemsList=(val)=>{
		if(Array.isArray(val)){
			return Array.concat.apply([],val.map(getNavtItemsList));
		}
		else if(val instanceof HTMLCollection || val instanceof NodeList){
			return [val];
		}
		else{
			return [val.children];
		}
	}
	if(app.param.nav){
		app.param.navItemsList=getNavtItemsList(app.param.nav);
		app.param.navItemsList.forEach(registerAsNav);
	}
	if(app.param.controls){
		if(app.param.controls.prev){app.param.controls.prev.addEventListener('click',app.prev);}
		if(app.param.controls.next){app.param.controls.next.addEventListener('click',app.next);}
	}
	el.addEventListener('scroll',debounce(updateActiveItem,100));
	window.addEventListener('resize',debounce(updateActiveItem,100));
	updateNavClass();
	updateActiveItem();
	return app;
}