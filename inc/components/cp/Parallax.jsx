export const Parallax=(props)=>{
	const {useState,useCallback,useEffect,useRef}=React;
	const {className='cp-parallax'}=props;
	const ref=useRef();
	
	useEffect(()=>{
		if(!ref.current){return;}
		var coef=1/(window.innerHeight-ref.current.clientHeight);
		window.addEventListener('resize',()=>{
			coef=1/(window.innerHeight-ref.current.clientHeight);
		});
		const tick=(t)=>{
			ref.current.style.setProperty('--parallax-p',ref.current.getBoundingClientRect().top*coef);
			window.requestAnimationFrame(tick);
		}
		window.requestAnimationFrame(tick);
	},[ref.current]);
	
	return (
		<div className={className} ref={ref}>
			{props.children}
		</div>
	);
}