package com.maikoo.superminercions.model.dto;

import com.fasterxml.jackson.annotation.JsonInclude;
import com.fasterxml.jackson.databind.annotation.JsonSerialize;
import com.maikoo.superminercions.model.WithdrawalOrderDO;
import com.maikoo.superminercions.serialize.NullStringSerializer;
import com.maikoo.superminercions.util.ConstantUtil;
import com.maikoo.superminercions.util.StatusUtil;
import com.maikoo.superminercions.util.TimeUtil;
import lombok.Data;

import java.math.BigDecimal;

@JsonInclude(JsonInclude.Include.NON_NULL)
@Data
public class SMCWithdrawalWithCustomerDTO {
    private long listSn;
    private String userName;
    private String userMobile;
    private BigDecimal smcNum;
    private BigDecimal smcWalletNum;
    private BigDecimal handlingFee;
    private BigDecimal actualFee;
    private String extractTo;
    private String status;
    private String applyAt;
    @JsonSerialize(nullsUsing = NullStringSerializer.class)
    private String finishAt;
    @JsonSerialize(nullsUsing = NullStringSerializer.class)
    private String remark;

    public static SMCWithdrawalWithCustomerDTO valueOf(WithdrawalOrderDO withdrawalOrderDO) {
        SMCWithdrawalWithCustomerDTO smcWithdrawalWithCustomerDTO = new SMCWithdrawalWithCustomerDTO();
        smcWithdrawalWithCustomerDTO.setListSn(withdrawalOrderDO.getOrderSN());
        smcWithdrawalWithCustomerDTO.setUserName(withdrawalOrderDO.getCustomerDO().getName());
        smcWithdrawalWithCustomerDTO.setUserMobile(withdrawalOrderDO.getCustomerDO().getPhone());
        smcWithdrawalWithCustomerDTO.setSmcNum(withdrawalOrderDO.getQuantity());
        smcWithdrawalWithCustomerDTO.setSmcWalletNum(withdrawalOrderDO.getSmcBalance());
        smcWithdrawalWithCustomerDTO.setHandlingFee(withdrawalOrderDO.getFee());
        smcWithdrawalWithCustomerDTO.setActualFee(withdrawalOrderDO.getPrice().multiply(withdrawalOrderDO.getQuantity()).subtract(withdrawalOrderDO.getFee()).setScale(6, BigDecimal.ROUND_HALF_EVEN));
        smcWithdrawalWithCustomerDTO.setExtractTo(method(withdrawalOrderDO.getMethod()));
        smcWithdrawalWithCustomerDTO.setStatus(StatusUtil.commonOrderStatus(withdrawalOrderDO.getStatus()));
        smcWithdrawalWithCustomerDTO.setApplyAt(TimeUtil.timeStampToDateTime(withdrawalOrderDO.getCreatedAt()).format(ConstantUtil.ORDER_DATE_TIME_PATTERN));
        smcWithdrawalWithCustomerDTO.setRemark(withdrawalOrderDO.getNote());
        if (withdrawalOrderDO.getStatus() == ConstantUtil.ORDER_STATUS_COMPLETED || withdrawalOrderDO.getStatus() == ConstantUtil.ORDER_STATUS_REJECTED) {
            smcWithdrawalWithCustomerDTO.setFinishAt(TimeUtil.timeStampToDateTime(withdrawalOrderDO.getUpdatedAt()).format(ConstantUtil.ORDER_DATE_TIME_PATTERN));
        }
        return smcWithdrawalWithCustomerDTO;
    }

    private static String method(int method) {
        if (method == 0) {
            return "alipay";
        } else if (method == 1) {
            return "bank";
        } else {
            throw new RuntimeException("无效的数据");
        }
    }
}
