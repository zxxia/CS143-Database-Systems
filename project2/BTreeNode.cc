#include "BTreeNode.h"


/* include by me*/
#include <string.h>

using namespace std;

/*
 * BTLeafNode constructor
 */
BTLeafNode::BTLeafNode()
{
	int c = 0;
	memset(buffer, c, PageFile::PAGE_SIZE);
}

/*
 * Read the content of the node from the page pid in the PageFile pf.
 * @param pid[IN] the PageId to read
 * @param pf[IN] PageFile to read from
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::read(PageId pid, const PageFile& pf)
{
	RC rc;
	rc = pf.read(pid, buffer);
	return rc; 
}

/*
 * Write the content of the node to the page pid in the PageFile pf.
 * @param pid[IN] the PageId to write to
 * @param pf[IN] PageFile to write to
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::write(PageId pid, PageFile& pf)
{
	RC rc;
	rc = pf.write(pid, buffer);
	return rc;
}

/*
 * Return the number of keys stored in the node.
 * @return the number of keys in the node
 */
int BTLeafNode::getKeyCount()
{
	int* keyCountPtr = (int*)buffer;
	return *keyCountPtr;
}

/*
 * Insert a (key, rid) pair to the node.
 * @param key[IN] the key to insert
 * @param rid[IN] the RecordId to insert
 * @return 0 if successful. Return an error code if the node is full.
 */
RC BTLeafNode::insert(int key, const RecordId& rid)
{
	int keyCount = getKeyCount();
	if(keyCount == 84)	return RC_NODE_FULL;
	int low = 0, high = keyCount - 1, mid = 0;
	unsigned int pair_size = sizeof(int) + sizeof(RecordId);
	while(low<=high) {
		mid = (low + high)/2;
		int* midKeyPtr = (int*)(buffer + sizeof(int) + sizeof(PageId) + pair_size * mid);
		if (key < *midKeyPtr){
			if (mid == 0 || *((int*)(buffer + sizeof(int)+sizeof(PageId)+pair_size *(mid - 1))) < key)
				break;
			high = mid - 1;
		}
		else{
			low = mid + 1;
			if (low>high) mid = low;
		}
	}	

	char* insert_addr = buffer + sizeof(int) + sizeof(PageId) + pair_size * mid;
	memmove(insert_addr + pair_size, insert_addr, pair_size * (keyCount-mid));
	memcpy(insert_addr , &key, sizeof(int));
	memcpy(insert_addr + sizeof(int), &rid, sizeof(RecordId));
	keyCount++;
	memcpy(buffer, &keyCount, sizeof(int));
	
	return 0;
}

/*
 * Insert the (key, rid) pair to the node
 * and split the node half and half with sibling.
 * The first key of the sibling node is returned in siblingKey.
 * @param key[IN] the key to insert.
 * @param rid[IN] the RecordId to insert.
 * @param sibling[IN] the sibling node to split with. This node MUST be EMPTY when this function is called.
 * @param siblingKey[OUT] the first key in the sibling node after split.
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::insertAndSplit(int key, const RecordId& rid, 
	BTLeafNode& sibling, int& siblingKey)
{
	RC rc;
	// necessary to check if the sibling node is empty ?

	unsigned int pair_size = sizeof(int) + sizeof(RecordId);
	char* cursor = buffer + sizeof(int) + sizeof(PageId*) + 84/2 * pair_size;
	siblingKey = *((int*)cursor);

	for (int i = 0; i < 84/2; i++) {		
		int* keyPtr = (int*)cursor;
		RecordId* recordPtr = (RecordId*)(cursor + sizeof(int));
		rc = sibling.insert(*keyPtr, *recordPtr);
		cursor += pair_size;
	}
	int keyCount = 42;
	memcpy(buffer, &keyCount, sizeof(int));

	if(key > siblingKey)
		sibling.insert(key, rid);
	else
		insert(key, rid);

	return rc;
}

/**
 * If searchKey exists in the node, set eid to the index entry
 * with searchKey and return 0. If not, set eid to the index entry
 * immediately after the largest index key that is smaller than searchKey,
 * and return the error code RC_NO_SUCH_RECORD.
 * Remember that keys inside a B+tree node are always kept sorted.
 * @param searchKey[IN] the key to search for.
 * @param eid[OUT] the index entry number with searchKey or immediately
                   behind the largest key smaller than searchKey.
 * @return 0 if searchKey is found. Otherwise return an error code.
 */
RC BTLeafNode::locate(int searchKey, int& eid)
{
	int keyCount = getKeyCount(), low = 0, high = keyCount - 1, mid;
	unsigned int pair_size = sizeof(int) + sizeof(RecordId);
	while(low<=high) {
		mid = (low+high)/2;
		int* midKeyPtr = (int*)(buffer + sizeof(int) + sizeof(PageId) + pair_size * mid);		
		if(searchKey == *midKeyPtr) {
			eid = mid;
			return 0;
		}
		else if (searchKey < *midKeyPtr){
			if (mid == 0 || *((int*)(buffer + sizeof(int)+sizeof(PageId)+pair_size*(mid - 1))) < searchKey){
				eid = mid;
				return RC_NO_SUCH_RECORD;
			}
			high = mid - 1;
		}
		else{
			if (mid == keyCount - 1){
				eid = keyCount;
				return RC_NO_SUCH_RECORD;
			}
			low = mid + 1;
		}
	}
	return 0;
}

/*
 * Read the (key, rid) pair from the eid entry.
 * @param eid[IN] the entry number to read the (key, rid) pair from
 * @param key[OUT] the key from the entry
 * @param rid[OUT] the RecordId from the entry
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::readEntry(int eid, int& key, RecordId& rid)
{
	unsigned int pair_size = sizeof(int) + sizeof(RecordId);
	int* keyPtr = (int*) (buffer + sizeof(int) + sizeof(PageId) + eid * pair_size);
	RecordId* recordIdPtr = (RecordId*) (buffer + sizeof(int) + sizeof(PageId) + eid * pair_size + sizeof(int));
	key = *keyPtr;
	rid = *recordIdPtr;
	return 0;
}

/*
 * Return the pid of the next slibling node.
 * @return the PageId of the next sibling node 
 */
PageId BTLeafNode::getNextNodePtr()
{
	PageId* nextNodePtr = (PageId*)(buffer + sizeof(int));
	return *nextNodePtr;
}

/*
 * Set the pid of the next slibling node.
 * @param pid[IN] the PageId of the next sibling node 
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::setNextNodePtr(PageId pid)
{
	memcpy(buffer + sizeof(int), &pid, sizeof(PageId));
	return 0;
}






/*
 * BTNonLeafNode constructor
 */
BTNonLeafNode::BTNonLeafNode()
{
	int c = 0;
	memset(buffer, c, PageFile::PAGE_SIZE);
}

/*
 * Read the content of the node from the page pid in the PageFile pf.
 * @param pid[IN] the PageId to read
 * @param pf[IN] PageFile to read from
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::read(PageId pid, const PageFile& pf)
{ 
	RC rc;
	rc = pf.read(pid, buffer);
	return rc;
}

/*
 * Write the content of the node to the page pid in the PageFile pf.
 * @param pid[IN] the PageId to write to
 * @param pf[IN] PageFile to write to
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::write(PageId pid, PageFile& pf)
{
	RC rc;
	rc = pf.write(pid, buffer);
	return rc;
}

/*
 * Return the number of keys stored in the node.
 * @return the number of keys in the node
 */
int BTNonLeafNode::getKeyCount()
{
	int* keyCountPtr = (int*)buffer;
	return *keyCountPtr;
}


/*
 * Insert a (key, pid) pair to the node.
 * @param key[IN] the key to insert
 * @param pid[IN] the PageId to insert
 * @return 0 if successful. Return an error code if the node is full.
 */
RC BTNonLeafNode::insert(int key, PageId pid)
{ 
	const unsigned int intsize = sizeof(int);
	int keyCount = getKeyCount();
	if (keyCount == 84) return RC_NODE_FULL;
	int low = 0, high = keyCount-1, mid=0;
	while (low <= high){
		mid = (low + high) / 2;
		int *temp = (int*)(buffer + 2 * (mid+1)*intsize);
		if (*temp < key) {
			low = mid + 1;
			if (low > high)
				mid = low;
		}
		else {
			if (mid == 0 || *((int*)(buffer + 2 * mid*intsize)) < key)
				break;
			else
				high = mid - 1;
		}			
	}
	memmove(buffer + 2 * (mid + 2) * intsize, buffer + 2 * (mid + 1) * intsize, 2 * intsize * (keyCount - mid));
	memcpy(buffer + 2 * (mid + 1) * intsize, &key, intsize);
	memcpy(buffer + 2 * (mid + 1)*intsize + intsize, &pid, intsize);
	keyCount++;
	memcpy(buffer, &keyCount, intsize);
	return 0;
}

/*
 * Insert the (key, pid) pair to the node
 * and split the node half and half with sibling.
 * The middle key after the split is returned in midKey.
 * @param key[IN] the key to insert
 * @param pid[IN] the PageId to insert
 * @param sibling[IN] the sibling node to split with. This node MUST be empty when this function is called.
 * @param midKey[OUT] the key in the middle after the split. This key should be inserted to the parent node.
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::insertAndSplit(int key, PageId pid, BTNonLeafNode& sibling, int& midKey)
{ 
	//actually we store 0-41 42-83 pageids after the insertion
	RC rc;
	static const unsigned int intsize = sizeof(int);
	int* mkey = (int*)(buffer + 2 * 43 * intsize);

	char* splitaddress;
	if ((key<*mkey) && (key>*(mkey - 2))){
		splitaddress = buffer + 2 * (42 + 1) * intsize;
		midKey = key;
		sibling.initializeRoot(pid, *((int*)splitaddress), *((PageId*)(splitaddress + intsize)));
		for (int i = 1; i < 42; i++){
			splitaddress += 2 * intsize;
			int *key1 = (int*)splitaddress;
			PageId *pid1 = (PageId*)(splitaddress + intsize);
			sibling.insert(*key1, *pid1);				
		}
		int count = 42;
		memcpy(buffer, &count, intsize);
	}
	else if (key < *mkey){
		splitaddress = buffer + 2 * (41+1) * intsize;
		sibling.initializeRoot(*((PageId*)(splitaddress + intsize)), *((int*)(splitaddress + 2 * intsize)), *((PageId*)(splitaddress + 3 * intsize)));
		midKey = *((int*)(splitaddress));
		splitaddress += 4 * intsize;
		for (int i = 2; i < 43; i++){
			int *key1 = (int*)splitaddress;
			PageId *pid1 = (PageId*)(splitaddress + intsize);
			sibling.insert(*key1, *pid1);
			splitaddress += 2 * intsize;
		}
		int count = 41;
		memcpy(buffer, &count, intsize);
		insert(key, pid);				
	}
	else{
		splitaddress = buffer + 2 * (42+1) * intsize;
		midKey = *((int*)(splitaddress));
		sibling.initializeRoot(*((PageId*)(splitaddress + intsize)), *((int*)(splitaddress + 2 * intsize)), *((PageId*)(splitaddress + 3 * intsize)));
		splitaddress += 4 * intsize;
		for (int i = 2; i < 42; i++){
			int *key1 = (int*)splitaddress;
			PageId *pid1 = (PageId*)(splitaddress + intsize);
			sibling.insert(*key1, *pid1);
			splitaddress += 2 * intsize;
		}
		sibling.insert(key, pid);
		int count = 42;
		memcpy(buffer, &count, intsize);
	}
	return 0; 
}

/*
 * Given the searchKey, find the child-node pointer to follow and
 * output it in pid.
 * @param searchKey[IN] the searchKey that is being looked up.
 * @param pid[OUT] the pointer to the child node to follow.
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::locateChildPtr(int searchKey, PageId& pid)
{ 
	static const unsigned int intsize = sizeof(int);
	int keyCount = getKeyCount(), low=0, high=keyCount-1, mid;
	if (searchKey < *((int*)(buffer + 2 * intsize))) {
		pid = *((PageId*)(buffer + intsize));
		return 0;
	}
	while (low <= high){
		mid = (low + high) / 2;
		int *temp = (int*)(buffer + 2*intsize + 2 * mid*intsize);
		if (*temp>searchKey)
			high = mid - 1;
		else {
			if ((mid == (keyCount-1)) || *((int*)(buffer + 4*intsize + 2 * mid*intsize))>searchKey)
				break;
			low = mid+1;
		}
	}
	pid = *((PageId*)(buffer + intsize*3 + 2 * intsize*mid));
	return 0;
}

/*
 * Initialize the root node with (pid1, key, pid2).
 * @param pid1[IN] the first PageId to insert
 * @param key[IN] the key that should be inserted between the two PageIds
 * @param pid2[IN] the PageId to insert behind the key
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::initializeRoot(PageId pid1, int key, PageId pid2)
{ 
	int count = 1;
	static const unsigned int intsize = sizeof(int);
	memcpy(buffer, &count, intsize);
	memcpy(buffer + intsize, &pid1, intsize);
	memcpy(buffer + 2 * intsize, &key, intsize);
	memcpy(buffer + 3 * intsize, &pid2, intsize);
	return 0; 
}
