package com.maikoo.superminercions.service;

import com.maikoo.superminercions.model.dto.SMCFeeDTO;
import com.maikoo.superminercions.model.dto.SMCTradingDTO;
import com.maikoo.superminercions.model.dto.SMCTradingInformationDTO;
import com.maikoo.superminercions.model.dto.SMCTradingWithCustomerDTO;
import com.maikoo.superminercions.model.query.OrderQuery;
import com.maikoo.superminercions.model.query.SMCTradingQuery;

import java.util.List;

public interface SMCTradingService {
    void buy(SMCTradingQuery smcTradingQuery);

    void sell(SMCTradingQuery smcTradingQuery);

    List<SMCTradingDTO> list(int pageNumber, int type);

    SMCTradingInformationDTO information(long orderSN);

    SMCFeeDTO fee(String type);

    List<SMCTradingWithCustomerDTO> listAll(int type);

    void updateBuyStatus(OrderQuery orderQuery);

    void updateSellStatus(OrderQuery orderQuery);

    void updateWithdrawalStatus(OrderQuery orderQuery);

    void updateLockStatus(OrderQuery orderQuery);

    void updateBuyNote(OrderQuery orderQuery);

    void updateSellNote(OrderQuery orderQuery);

    void updateWithdrawalNote(OrderQuery orderQuery);
}
