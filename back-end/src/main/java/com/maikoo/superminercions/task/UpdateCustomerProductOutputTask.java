package com.maikoo.superminercions.task;

import com.maikoo.superminercions.service.CustomerProductService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.scheduling.annotation.Scheduled;
import org.springframework.stereotype.Component;

@Component
public class UpdateCustomerProductOutputTask {
    @Autowired
    private CustomerProductService customerProductService;

    @Scheduled(cron = "1 0 0 * * ?")
    public void run(){
        customerProductService.updateCustomerProductOutput();
    }
}
