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
	animate:(cb,dur=500)=>{
		var s=parseInt(performance.now()),c=1/dur,p=0;
		const tick=(t)=>{
			p=(t-s)*c;
			if(p>1){return cb(1);}
			window.requestAnimationFrame(tick);
			return cb(CP.ease(p));
		}
		window.requestAnimationFrame(tick);
	},
	ease:(p)=>(p<0.5)?(p*p*2):(1-Math.pow(1-p,2)*2)
};
window.CP=CP;