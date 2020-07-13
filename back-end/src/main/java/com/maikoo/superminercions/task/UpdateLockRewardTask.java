package com.maikoo.superminercions.task;

import com.maikoo.superminercions.service.SMCLockService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.scheduling.annotation.Scheduled;
import org.springframework.stereotype.Component;

@Component
public class UpdateLockRewardTask {
    @Autowired
    private SMCLockService smcLockService;

    @Scheduled(cron = "0 30 0 * * ?")
    public void run(){
        smcLockService.updateLockReward();
    }
}
