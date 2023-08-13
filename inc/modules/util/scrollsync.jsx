export const scrollsync=function(el,param={}){
	const app={};
	app.param=Object.assign({nav:false,direction:'y',margin:0},param);
	const items=el.children;
	const updateActiveItem=()=>{
		let i,index=-1;
		const l=el.children.length;
		const bnd=el.getBoundingClientRect();
		const cx=bnd.left+bnd.width/2,cy=bnd.top+bnd.height/2;
		for(i=0;i<l;i++){
			const bnd=el.children[i].getBoundingClientRect();
			if(bnd.left<cx && bnd.top<cy && bnd.right>cx && bnd.bottom>cy){
				index=i;
				break;
			}
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
		el.scrollBy({[app.param.direction==='x'?'left':'top']:bnd2.top-bnd1.top+app.param.margin});
		updateActiveItem();
	};
	app.prev=()=>app.goto(app.current-1);
	app.next=()=>app.goto(app.current+1);
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
	app.timer=setInterval(updateActiveItem,100);
	updateActiveItem();
	return app;
}