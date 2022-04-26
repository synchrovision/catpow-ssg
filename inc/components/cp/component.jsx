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
	}
};
window.CP=CP;