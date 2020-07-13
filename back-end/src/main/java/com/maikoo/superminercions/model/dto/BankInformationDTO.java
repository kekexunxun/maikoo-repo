package com.maikoo.superminercions.model.dto;

import com.maikoo.superminercions.model.CustomerDO;
import lombok.Data;

@Data
public class BankInformationDTO {
    private String bankCard;
    private String bankName;
    private String bankBranch;

    public static BankInformationDTO valueOf(CustomerDO customerDO) {
        BankInformationDTO bankInformationDTO = new BankInformationDTO();
        bankInformationDTO.setBankName(customerDO.getBank());
        bankInformationDTO.setBankBranch(customerDO.getBankBranch());
        bankInformationDTO.setBankCard(customerDO.getBankCardNumber());
        return bankInformationDTO;
    }
}
