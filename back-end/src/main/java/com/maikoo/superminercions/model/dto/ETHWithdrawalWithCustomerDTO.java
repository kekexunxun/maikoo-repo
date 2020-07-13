package com.maikoo.superminercions.model.dto;

import com.fasterxml.jackson.annotation.JsonUnwrapped;
import com.maikoo.superminercions.model.ETHWithdrawalDO;
import lombok.Data;

@Data
public class ETHWithdrawalWithCustomerDTO {
    private String userName;
    private String userMobile;
    @JsonUnwrapped
    private ETHWithdrawalInformationDTO ethWithdrawalInformationDTO;

    public static ETHWithdrawalWithCustomerDTO valueOf(ETHWithdrawalDO ethWithdrawalDO) {
        ETHWithdrawalWithCustomerDTO ethWithdrawalWithCustomerDTO = new ETHWithdrawalWithCustomerDTO();
        ethWithdrawalWithCustomerDTO.setEthWithdrawalInformationDTO(ETHWithdrawalInformationDTO.valueOf(ethWithdrawalDO));
        ethWithdrawalWithCustomerDTO.setUserName(ethWithdrawalDO.getCustomerDO().getName());
        ethWithdrawalWithCustomerDTO.setUserMobile(ethWithdrawalDO.getCustomerDO().getPhone());
        if (ethWithdrawalWithCustomerDTO.getEthWithdrawalInformationDTO().getRemark() == null) {
            ethWithdrawalWithCustomerDTO.getEthWithdrawalInformationDTO().setRemark(ethWithdrawalDO.getNote() == null ? "" : ethWithdrawalDO.getNote());
        }
        if (ethWithdrawalWithCustomerDTO.getEthWithdrawalInformationDTO().getFinishAt() == null) {
            ethWithdrawalWithCustomerDTO.getEthWithdrawalInformationDTO().setFinishAt("");
        }
        return ethWithdrawalWithCustomerDTO;
    }
}
