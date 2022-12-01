import {bem} from 'util';

export const ElasticBox=(props)=>{
	const {useMemo,useState,useCallback,useEffect,useRef}=React;
	const {className="cp-elasticbox",children}=props;
	const [current,setCurrent]=useState(props.initialOpen || 0);
	const classes=useMemo(()=>bem(className),[className]);
	
	const ref=useRef({});
	
	const [height,setHeight]=useState('auto');
	
	useEffect(()=>{
		const observer=new ResizeObserver((entries)=>{
			setHeight(entries[0].contentRect.height + 'px');
		});
		observer.observe(ref.current.children[0]);
	},[ref.current]);
	
	return (
		<div className={classes()} ref={ref} style={{height}}>
			<div className={classes._body()}>
				{props.children}
			</div>
		</div>
	);
}