package com.maikoo.businessdirectory.task;

import com.maikoo.businessdirectory.util.WechatUtil;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.scheduling.annotation.Scheduled;
import org.springframework.stereotype.Component;

@Component
public class WechatAccessTokenTask {
    @Autowired
    private WechatUtil wechatUtil;

    @Scheduled(fixedRate =  105 * 60 * 1000)
    public void run(){
        wechatUtil.accessToken();
    }
}
