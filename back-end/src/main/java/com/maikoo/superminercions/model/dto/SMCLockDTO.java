package com.maikoo.superminercions.model.dto;

import com.maikoo.superminercions.model.SMCLockDO;
import com.maikoo.superminercions.util.StatusUtil;
import lombok.Data;

import java.math.BigDecimal;

@Data
public class SMCLockDTO {
    private long listSn;
    private BigDecimal smcNum;
    private int lpDate;
    private String status;

    public static SMCLockDTO valueOf(SMCLockDO smcLockDO){
        SMCLockDTO smcLockDTO = new SMCLockDTO();
        smcLockDTO.setListSn(smcLockDO.getOrderSN());
        smcLockDTO.setSmcNum(smcLockDO.getQuantity());
        smcLockDTO.setLpDate(smcLockDO.getSmcLockCycleDO().getCycle());
        smcLockDTO.setStatus(StatusUtil.commonOrderStatus(smcLockDO.getStatus()));
        return smcLockDTO;
    }
}
