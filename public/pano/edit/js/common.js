
var language={};
var xml_data={
    "global_setting":[],
}
//xml加载完成 转化数据
function xml_data_loding(){
    xml_data.global_setting=krpano.get("global_setting[global_setting]");
    //读取语言包 zh中文en英文tw繁体
    language=language_pack[xml_data.global_setting.zh]
    scene_loding();
}
//场景加载
function scene_loding(){
    let global_setting=xml_data.global_setting;
    //开场动画
    if(global_setting.open_animation&&special_state('open_animation')){
        FileLoading.js('/static/open_animation/index.js',function (){
            let open_animation_data=JSON.parse(global_setting.open_animation_data);
            global_setting.open_animation_data=open_animation_data;
            open_animation(global_setting.open_animation,global_setting.open_animation_data)
        });
    }
}
//场景开始
function scene_start(){
    $("#pano").show();
    //全局总览 云游
    if(global_setting.overview&&special_state('overview')){
        //调用全局总览和云游的方法
    }
}
//特殊状态判断 根据模式不同判断
function special_state(type){
    return true;
}
//场景加载完成后执行
function scene_ready(){

}
//右键添加
function right_click(){
    //获取当前鼠标坐标
    let x=krpano.get("mouse.x");
    let y=krpano.get("mouse.y");
    let coordinate=krpano.screentosphere(x,y);
    let ath=coordinate.x
    let atv=coordinate.y
    let data={
        "ath":ath,
        "atv":atv,
        "width":60,
        "height":60,
        "type":"image",
        "url":"/pano/edit/images/design.svg",
    }
    add_hotspot("design",data);
}
//添加热点
function add_hotspot(name,data) {
    krpano.call("addhotspot("+name+")");
    let hotspot="hotspot["+name+"]";
    $.each(data,function (i,item) {
        if(i!="point"){
            krpano.set(hotspot+"."+i,item);
        }else{
            for(let j=0;j<item.length;j++){
                krpano.set(hotspot+".point["+j+"].ath",item[j].ath);
                krpano.set(hotspot+".point["+j+"].atv",item[j].atv);
            }
        }
    });
}