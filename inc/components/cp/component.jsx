export const CP={
	nl2br:(text)=>{
		return text.split(/(\n)/).map((line,index)=>line==="\n"?<br/>:line);
	},
	wordsToFlags:(words)=>{
		var rtn={};
		if(undefined === words){return {};}
		words.split(' ').map((word)=>{rtn[word]=true;});
		return rtn;
	},
	flagsToWords:(flags)=>{
		if(undefined === flags){return '';}
		return Object.keys(flags).filter((word)=>flags[word]).join(' ');
	},
	renderComponents:()=>{
		const list=document.querySelectorAll('[data-cp-component]');
		Array.prototype.forEach.call(list,(el)=>{
			ReactDOM.render(React.createElement(Catpow[el.dataset.cpComponent]),CP.extractPropsFromElement(el));
		});
	},
	extractPropsFromElement:(el)=>{
		return {...el.dataset};
	},
	scrollTo:(tgt)=>{
		const s=parseInt(window.scrollY),d=tgt-s;
		CP.animate((p)=>window.scrollTo(0,s+d*p));
	},
	animate:(cb,dur=500,ease=null)=>{
		var s=parseInt(performance.now()),c=1/dur,p=0;
		if(ease===null){ease=CP.ease;}
		if(Array.isArray(ease)){
			const ns=ease;
			ns.unshift(0);ns.push(1);
			ease=(p)=>CP.bez(ns,p);
		}
		const tick=(t)=>{
			p=(t-s)*c;
			if(p>1){return cb(1);}
			window.requestAnimationFrame(tick);
			return cb(ease(p));
		}
		window.requestAnimationFrame(tick);
	},
	ease:(p)=>(p<0.5)?(p*p*2):(1-Math.pow(1-p,2)*2),
	bez:(ns,t)=>{
		var p=0,n=ns.length-1,i;
		p+=ns[0]*Math.pow((1-t),n)
		for(i=1;i<n;i++){
			p+=ns[i]*Math.pow((1-t),n-i)*Math.pow(t,i)*n;
		}
		p+=ns[n]*Math.pow(t,n);
		return p;
	}
};
window.CP=CP;