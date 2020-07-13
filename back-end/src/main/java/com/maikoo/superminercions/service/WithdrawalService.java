package com.maikoo.superminercions.service;

import com.maikoo.superminercions.model.dto.SMCWithdrawalWithCustomerDTO;
import com.maikoo.superminercions.model.dto.WithdrawalOrderDTO;
import com.maikoo.superminercions.model.dto.WithdrawalOrderInformationDTO;
import com.maikoo.superminercions.model.query.WithdrawalOrderQuery;

import java.util.List;

public interface WithdrawalService {
    void withdrawal(WithdrawalOrderQuery withdrawalOrderQuery);

    List<WithdrawalOrderDTO> list(int pageNumber);

    WithdrawalOrderInformationDTO information(long orderSN);

    List<SMCWithdrawalWithCustomerDTO> listAll();
}
