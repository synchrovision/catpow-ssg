export const renderComponents=()=>{
	const list=document.querySelectorAll('[data-catpow-component]');
	Array.prototype.forEach.call(list,(el)=>{
		ReactDOM.render(React.createElement(Catpow[el.dataset.cpComponent]),{...el.dataset});
	});
};