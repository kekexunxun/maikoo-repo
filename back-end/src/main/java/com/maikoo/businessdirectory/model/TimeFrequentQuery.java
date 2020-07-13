package com.maikoo.businessdirectory.model;

import lombok.Data;

@Data
public class TimeFrequentQuery {
    private long startTime;
    private long endTime;

    public TimeFrequentQuery() {
    }

    public TimeFrequentQuery(long startTime, long endTime) {
        this.startTime = startTime;
        this.endTime = endTime;
        System.out.println("startTime:"+startTime+",endTime:"+endTime);
    }
}
