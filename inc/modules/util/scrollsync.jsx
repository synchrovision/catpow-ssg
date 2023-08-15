export const scrollsync=function(el,param={}){
	const app={};
	app.param=Object.assign({nav:false},param);
	if(!app.param.direction){
		app.param.direction=(el.clientHeight<el.scrollHeight)?'y':'x';
	}
	if(!app.param.position){
		const ssa=getComputedStyle(el.children[0])['scroll-snap-align'];
		app.param.position={start:0,center:0.5,end:1}[ssa] || 0;
	}
	const k1={x:'left',y:'top'}[app.param.direction];
	const k2={x:'right',y:'bottom'}[app.param.direction];
	const k3={x:'scrollLeft',y:'scrollTop'}[app.param.direction];
	const k4={x:'width',y:'height'}[app.param.direction];
	const items=el.children;
	const updateActiveItem=()=>{
		let i,index=-1;
		const l=el.children.length;
		const bnd=el.getBoundingClientRect();
		const c=bnd[k1]+bnd[k4]*app.param.position;
		for(i=0;i<l;i++){
			const bnd=el.children[i].getBoundingClientRect();
			if(bnd[k2]>c){index=i;break;}
		}
		updateItemsClass(el.children,index);
		if(app.param.nav){
			if(Array.isArray(app.param.nav)){
				app.param.nav.forEach((target)=>{updateItemsClass(target.children,index);});
			}
			else{
				updateItemsClass(app.param.nav.children,index);
			}
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
	app.goto=(index)=>{
		const bnd1=el.getBoundingClientRect();
		const bnd2=el.children[index].getBoundingClientRect();
		const p=app.param.position;
		el.scrollTo({
			[k1]:el[k3]+(bnd2[k1]+bnd2[k4]*p)-(bnd1[k1]+bnd1[k4]*p)
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
	if(app.param.nav){
		if(Array.isArray(app.param.nav)){
			app.param.nav.forEach((target)=>{registerAsNav(target.children);});
		}
		else{
			registerAsNav(app.param.nav.children);
		}
	}
	if(app.param.controls){
		if(app.param.controls.prev){app.param.controls.prev.addEventListener('click',app.prev);}
		if(app.param.controls.next){app.param.controls.next.addEventListener('click',app.next);}
	}
	app.timer=setInterval(updateActiveItem,100);
	updateActiveItem();
	return app;
}