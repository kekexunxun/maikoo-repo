package com.maikoo.superminercions.service;

import com.maikoo.superminercions.model.dto.SMCLockCycleDTO;
import com.maikoo.superminercions.model.dto.SMCLockDTO;
import com.maikoo.superminercions.model.dto.SMCLockInformationDTO;
import com.maikoo.superminercions.model.dto.SMCLockWithCustomerDTO;

import java.math.BigDecimal;
import java.util.List;

public interface SMCLockService {
    List<SMCLockCycleDTO> lockCycle();

    void apply(long lockCycleId, BigDecimal smcQuantity);

    List<SMCLockDTO> list(int pageNumber);

    SMCLockInformationDTO information(long orderSN);

    List<SMCLockWithCustomerDTO> listAll();

    void updateLockReward();
}
