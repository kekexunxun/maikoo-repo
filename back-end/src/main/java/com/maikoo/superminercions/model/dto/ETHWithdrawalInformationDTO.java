package com.maikoo.superminercions.model.dto;

import com.fasterxml.jackson.annotation.JsonInclude;
import com.fasterxml.jackson.annotation.JsonUnwrapped;
import com.maikoo.superminercions.model.ETHWithdrawalDO;
import com.maikoo.superminercions.util.ConstantUtil;
import com.maikoo.superminercions.util.TimeUtil;
import lombok.Data;

@Data
@JsonInclude(JsonInclude.Include.NON_NULL)
public class ETHWithdrawalInformationDTO {
    @JsonUnwrapped
    private ETHWithdrawalDTO ethWithdrawalDTO;
    private String walletAddress;
    private String applyAt;
    private String finishAt;
    private String remark;

    public static ETHWithdrawalInformationDTO valueOf(ETHWithdrawalDO ethWithdrawalDO) {
        ETHWithdrawalInformationDTO ethWithdrawalInformationDTO = new ETHWithdrawalInformationDTO();
        ethWithdrawalInformationDTO.setEthWithdrawalDTO(ETHWithdrawalDTO.valueOf(ethWithdrawalDO));
        ethWithdrawalInformationDTO.setWalletAddress(ethWithdrawalDO.getWalletAddress());
        ethWithdrawalInformationDTO.setApplyAt(TimeUtil.timeStampToDateTime(ethWithdrawalDO.getCreatedAt()).format(ConstantUtil.ORDER_DATE_TIME_PATTERN));
        if (ethWithdrawalDO.getStatus() == ConstantUtil.ORDER_STATUS_SMC_TRADING_COMPLETED || ethWithdrawalDO.getStatus() == ConstantUtil.ORDER_STATUS_SMC_TRADING_REJECTED) {
            ethWithdrawalInformationDTO.setFinishAt(TimeUtil.timeStampToDateTime(ethWithdrawalDO.getUpdatedAt()).format(ConstantUtil.ORDER_DATE_TIME_PATTERN));
        }

        if (ethWithdrawalDO.getStatus() == ConstantUtil.ORDER_STATUS_SMC_TRADING_REJECTED) {
            ethWithdrawalInformationDTO.setRemark(ethWithdrawalDO.getNote());
        }
        return ethWithdrawalInformationDTO;
    }
}
