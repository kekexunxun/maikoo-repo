package com.maikoo.superminercions;

import com.maikoo.superminercions.service.SMCLockService;
import org.junit.Test;
import org.junit.runner.RunWith;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.test.context.SpringBootTest;
import org.springframework.test.context.junit4.SpringRunner;

@RunWith(SpringRunner.class)
@SpringBootTest
public class SuperMinerCionsApplicationTests {
    @Autowired
    private SMCLockService smcLockService;

    @Test
    public void contextLoads() {
        smcLockService.updateLockReward();
    }

}
