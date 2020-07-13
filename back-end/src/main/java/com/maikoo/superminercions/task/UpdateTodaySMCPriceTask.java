package com.maikoo.superminercions.task;

import com.maikoo.superminercions.service.SMCPriceService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.scheduling.annotation.Scheduled;
import org.springframework.stereotype.Component;

@Component
public class UpdateTodaySMCPriceTask {
    @Autowired
    private SMCPriceService smcPriceService;

    @Scheduled(cron = "0 0 9 * * ?")
    public void run(){
        smcPriceService.updateTodaySMCPrice();
    }
}
