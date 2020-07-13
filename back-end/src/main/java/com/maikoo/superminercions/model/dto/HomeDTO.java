package com.maikoo.superminercions.model.dto;

import lombok.Data;

import java.math.BigDecimal;
import java.util.List;

@Data
public class HomeDTO {
    private NoticeDTO notice;
    private List<HomeSlideshowDTO> banner;
    private List<BigDecimal> chartsData;
    private BigDecimal todaySmcPrice;
}
